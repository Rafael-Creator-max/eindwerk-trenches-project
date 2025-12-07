<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\Cryptocurrency;
use App\Models\UserCryptoFollow;
use App\Services\CoinGeckoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

/**
 * @group Cryptocurrency Management
 *
 * APIs for managing cryptocurrencies and user interactions with them
 */

class CryptocurrencyController extends Controller
{
    protected $coinGeckoService;
    
    // Cache duration in minutes
    protected $cacheDuration = 5;

    /**
     * Create a new controller instance.
     *
     * @param  \App\Services\CoinGeckoService  $coinGeckoService
     * @return void
     */
    public function __construct(CoinGeckoService $coinGeckoService)
    {
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Get List of All Cryptocurrencies
     *
     * Returns a paginated list of all cryptocurrencies with their current market data.
     * Results are ordered by market cap (descending) and cached for 5 minutes to improve performance.
     *
     * @queryParam page integer The page number to return. Example: 1
     * @queryParam per_page integer Number of items per page. Default: 15. Example: 20
     * @queryParam vs_currency string The target currency of market data. Default: usd. Example: eur
     * @queryParam order string The field to order by. Default: market_cap_desc. Example: volume_desc
     * @queryParam sparkline boolean Include sparkline 7d data. Default: false. Example: true
     * @queryParam price_change_percentage string Include price change percentage. Comma-separated values. Example: 1h,24h,7d
     *
     * @response 200 {
     *   "data": [
     *     {
     *       "id": 1,
     *       "asset_type_id": 1,
     *       "symbol": "BTC",
     *       "name": "Bitcoin",
     *       "slug": "bitcoin",
     *       "external_id": "bitcoin",
     *       "current_price": 50000.00,
     *       "market_cap": 1000000000000.00,
     *       "market_cap_rank": 1,
     *       "total_volume": 50000000000.00,
     *       "high_24h": 51000.00,
     *       "low_24h": 49000.00,
     *       "price_change_24h": 1000.00,
     *       "price_change_percentage_24h": 2.04,
     *       "market_cap_change_24h": 20000000000.00,
     *       "market_cap_change_percentage_24h": 2.04,
     *       "circulating_supply": 18796831.0,
     *       "total_supply": 21000000.0,
     *       "max_supply": 21000000.0,
     *       "ath": 69045.00,
     *       "ath_change_percentage": -27.59,
     *       "ath_date": "2021-11-10T14:24:11.849Z",
     *       "atl": 67.81,
     *       "atl_change_percentage": 73630.19,
     *       "atl_date": "2013-07-06T00:00:00.000Z",
     *       "last_updated": "2023-01-01T12:00:00.000Z",
     *       "price_change_percentage_1h_in_currency": 0.1,
     *       "price_change_percentage_24h_in_currency": 2.04,
     *       "price_change_percentage_7d_in_currency": -5.24,
     *       "image": "https://assets.coingecko.com/coins/images/1/large/bitcoin.png",
     *       "roi": null,
     *       "sparkline_in_7d": {
     *         "price": [
     *           50000.00, 50200.00, 50100.00, 50300.00, 50400.00
     *         ]
     *       }
     *       "price_change_24h": "2.50000000",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
     *     },
     *     ...
     *   ]
     * }
     *
     * @group Cryptocurrency Management
     */
    public function index(Request $request)
    {
        $forceRefresh = $request->has('force_refresh') && $request->boolean('force_refresh');
        
        // If force refresh is requested, clear the cache and fetch fresh data
        if ($forceRefresh) {
            Cache::forget('cryptocurrencies_list');
            $this->fetchAndStoreCryptos();
        }
        
        // Try to get from cache first, unless force refresh was requested
        $cryptos = Cache::remember('cryptocurrencies_list', now()->addMinutes($this->cacheDuration), function () {
            $cachedData = Cryptocurrency::with('assetType')
                ->orderBy('market_cap', 'desc')
                ->get();
                
            // If no data in database, try to fetch from API
            if ($cachedData->isEmpty()) {
                $this->fetchAndStoreCryptos();
                return Cryptocurrency::with('assetType')
                    ->orderBy('market_cap', 'desc')
                    ->get();
            }
            
            return $cachedData;
        });

        return response()->json($cryptos);
    }

    /**
     * Get Cryptocurrency Details
     *
     * Returns detailed information about a specific cryptocurrency by its slug or id.
     * Data is cached for 5 minutes to improve performance.
     *
     * @urlParam id string required The slug or id of the cryptocurrency. Example: bitcoin
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "asset_type_id": 1,
     *     "symbol": "BTC",
     *     "name": "Bitcoin",
     *     "slug": "bitcoin",
     *     "external_id": "bitcoin",
     *     "current_price": "50000.00000000",
     *     "market_cap": "1000000000000.00",
     *     "volume_24h": "50000000000.00",
     *     "price_change_24h": "2.50000000",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z",
     *     "asset_type": {
     *       "id": 1,
     *       "name": "Cryptocurrency",
     *       "description": "Digital or virtual currency that uses cryptography for security",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
     *     },

    /**
     * Fetch and store cryptocurrencies from CoinGecko API
     */
    protected function fetchAndStoreCryptos()
    {
        $cryptos = $this->coinGeckoService->getCoinsMarkets([
            'per_page' => 10,
            'order' => 'market_cap_desc',
        ]);
        
        if ($cryptos) {
            foreach ($cryptos as $cryptoData) {
                $this->updateOrCreateCryptoFromApi($cryptoData);
            }
        }
    }
    
    /**
     * Update or create a cryptocurrency from API data
     */
    protected function updateOrCreateCryptoFromApi(array $cryptoData)
    {
        $assetType = \App\Models\AssetType::firstOrCreate(
            ['name' => 'Cryptocurrency'],
            ['description' => 'Digital or virtual currency that uses cryptography for security']
        );
        
        return Cryptocurrency::updateOrCreate(
            ['external_id' => $cryptoData['id']],
            [
                'asset_type_id' => $assetType->id,
                'symbol' => strtoupper($cryptoData['symbol'] ?? ''),
                'name' => $cryptoData['name'] ?? 'Unknown',
                'slug' => $cryptoData['id'],
                'current_price' => $cryptoData['current_price'] ?? 0,
                'market_cap' => $cryptoData['market_cap'] ?? 0,
                'volume_24h' => $cryptoData['total_volume'] ?? 0,
                'price_change_24h' => $cryptoData['price_change_percentage_24h_in_currency'] ?? 0,
            ]
        );
    }

    /**
     * Follow a Cryptocurrency
     *
     * Allows the authenticated user to follow a specific cryptocurrency.
     * Requires authentication via Bearer token.
     *
     * @authenticated
     * @urlParam cryptocurrency string required The slug of the cryptocurrency to follow. Example: bitcoin
     *
     * @response 200 {
     *   "message": "Successfully followed cryptocurrency"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\Cryptocurrency]"
     * }
     *
     * @group User Actions
     */
    public function follow($id)
    {
        $user = Auth::user();
        $identifier = request()->input('cryptocurrency', $id);
        $query = Cryptocurrency::query();
        if (is_numeric($identifier)) {
            $query->where('id', (int) $identifier);
        } else {
            $query->where('slug', $identifier);
        }
        $crypto = $query->firstOrFail();
        
        // Check if already following
        if (!$user->cryptocurrencies()->where('cryptocurrency_id', $crypto->id)->exists()) {
            $user->cryptocurrencies()->attach($crypto->id);
        }
        
        return response()->json(['message' => 'Successfully followed cryptocurrency']);
    }

    /**
     * Get Cryptocurrency Details
     *
     * Returns detailed information about a specific cryptocurrency by its slug or id.
     *
     * @urlParam id string required The slug or id of the cryptocurrency. Example: bitcoin
     *
     * @response 200 {
     *   "data": {
     *     "id": 1,
     *     "symbol": "BTC",
     *     "name": "Bitcoin",
     *     "slug": "bitcoin",
     *     "external_id": "bitcoin"
     *   }
     * }
     *
     * @response 404 {
     *   "message": "Cryptocurrency not found"
     * }
     *
     * @group Cryptocurrency Management
     */
    public function show($id)
    {
        $identifier = request()->input('cryptocurrency', $id);
        $query = Cryptocurrency::query()->with('assetType');
        if (is_numeric($identifier)) {
            $query->where('id', (int) $identifier);
        } else {
            $query->where('slug', $identifier);
        }
        $crypto = $query->firstOrFail();

        // Add price change 24h to the response if not already present
        if (!isset($crypto->price_change_24h)) {
            $crypto->price_change_24h = $crypto->price_change_24h ?? 0;
        }

        return response()->json(['data' => $crypto]);
    }

    /**
     * Get price history for a cryptocurrency
     *
     * @param string $id The cryptocurrency slug or ID
     * @param CoinGeckoService $coinGeckoService
     * @return \Illuminate\Http\JsonResponse
     * 
     * @response 200 {
     *   "data": {
     *     "prices": [[timestamp, price], ...],
     *     "market_caps": [[timestamp, market_cap], ...],
     *     "total_volumes": [[timestamp, volume], ...]
     *   },
     *   "crypto_id": 1,
     *   "symbol": "BTC",
     *   "name": "Bitcoin"
     * }
     * 
     * @response 200 {
     *   "data": {
     *     "prices": [],
     *     "market_caps": [],
     *     "total_volumes": []
     *   },
     *   "message": "No price data available",
     *   "crypto_id": 1,
     *   "symbol": "BTC"
     * }
     */
    public function priceHistory($id, CoinGeckoService $coinGeckoService)
    {
        $startTime = microtime(true);
        $logContext = [
            'endpoint' => 'priceHistory',
            'crypto_id' => $id,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ];

        try {
            // Find the cryptocurrency
            $identifier = request()->input('cryptocurrency', $id);
            $query = Cryptocurrency::query();
            if (is_numeric($identifier)) {
                $query->where('id', (int) $identifier);
            } else {
                $query->where('slug', $identifier);
            }
            $crypto = $query->firstOrFail();

            $logContext['crypto_id'] = $crypto->id;
            $logContext['external_id'] = $crypto->external_id;
            
            // Validate and get days parameter
            $days = request()->input('days', '7');
            $days = is_numeric($days) ? (int)$days : 7;
            $days = max(1, min(365, $days)); // Clamp between 1 and 365 days
            
            $logContext['days'] = $days;
            $logContext['request_params'] = request()->all();
            
            Log::info('Fetching price history', $logContext);
            
            // Get data from CoinGecko API with retry logic
            $history = $coinGeckoService->getPriceHistory($crypto->external_id, $days);
            $logContext['execution_time'] = round(microtime(true) - $startTime, 3) . 's';

            $responseData = [
                'data' => [
                    'prices' => [],
                    'market_caps' => [],
                    'total_volumes' => []
                ],
                'crypto_id' => $crypto->id,
                'symbol' => $crypto->symbol,
                'name' => $crypto->name,
                'last_updated' => now()->toIso8601String()
            ];

            if (!$history || empty($history['prices'])) {
                $logContext['status'] = 'no_data';
                Log::warning('No price history data available', $logContext);
                
                $responseData['message'] = 'No price data available';
                return response()->json($responseData);
            }
            
            // Process and validate the price data
            $prices = $history['prices'] ?? [];
            $marketCaps = $history['market_caps'] ?? [];
            $totalVolumes = $history['total_volumes'] ?? [];
            
            // Log statistics about the data
            $logContext['data_points'] = count($prices);
            $logContext['status'] = 'success';
            
            if (!empty($prices)) {
                $firstPoint = reset($prices);
                $lastPoint = end($prices);
                $logContext['date_range'] = [
                    'start' => date('Y-m-d H:i:s', $firstPoint[0] / 1000),
                    'end' => date('Y-m-d H:i:s', $lastPoint[0] / 1000)
                ];
                $logContext['price_range'] = [
                    'low' => min(array_column($prices, 1)),
                    'high' => max(array_column($prices, 1))
                ];
            }
            
            Log::info('Successfully fetched price history', $logContext);
            
            // Prepare the response
            $responseData['data'] = [
                'prices' => $prices,
                'market_caps' => $marketCaps,
                'total_volumes' => $totalVolumes
            ];
            
            return response()->json($responseData);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $logContext['error'] = 'Cryptocurrency not found';
            $logContext['exception'] = get_class($e);
            Log::error('Cryptocurrency not found', $logContext);
            
            return response()->json([
                'message' => 'Cryptocurrency not found',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 404);
            
        } catch (\Exception $e) {
            $logContext['error'] = $e->getMessage();
            $logContext['exception'] = get_class($e);
            $logContext['trace'] = config('app.debug') ? $e->getTraceAsString() : null;
            $logContext['execution_time'] = round(microtime(true) - $startTime, 3) . 's';
            
            Log::error('Error fetching price history: ' . $e->getMessage(), $logContext);
            
            // Return empty data structure on error
            return response()->json([
                'data' => [
                    'prices' => [],
                    'market_caps' => [],
                    'total_volumes' => []
                ],
                'message' => 'Unable to fetch price history at this time',
                'error' => config('app.debug') ? [
                    'message' => $e->getMessage(),
                    'type' => get_class($e)
                ] : null
            ], 500);
        }
    }

    /**
     * Get User's Followed Cryptocurrencies
     *
     * Returns a list of all cryptocurrencies that the authenticated user is following.
     * Requires authentication via Bearer token.
     *
     * @authenticated
     *
     * @response 200 [
     *   {
     *     "id": 1,
     *     "asset_type_id": 1,
     *     "symbol": "BTC",
     *     "name": "Bitcoin",
     *     "slug": "bitcoin",
     *     "external_id": "bitcoin",
     *     "current_price": "50000.00000000",
     *     "market_cap": "1000000000000.00",
     *     "volume_24h": "50000000000.00",
     *     "price_change_24h": "2.50000000",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z",
     *     "pivot": {
     *       "user_id": 1,
     *       "cryptocurrency_id": 1
     *     },
     *     "asset_type": {
     *       "id": 1,
     *       "name": "Cryptocurrency",
     *       "description": "Digital or virtual currency that uses cryptography for security",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
     *     }
     *   }
     * ]
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @group User Actions
     */
    public function followed()
    {
        try {
            $user = Auth::user();
            
            $cryptos = $user->cryptocurrencies()
                ->with('assetType')
                ->orderBy('market_cap', 'desc')
                ->get();
                
            return response()->json($cryptos);
            
        } catch (\Exception $e) {
            Log::error('Error fetching followed cryptocurrencies: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'exception' => get_class($e),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ]);
            
            return response()->json([
                'message' => 'Unable to fetch followed cryptocurrencies',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    
    /**
     * Unfollow a Cryptocurrency
     *
     * Allows the authenticated user to unfollow a specific cryptocurrency.
     * Requires authentication via Bearer token.
     *
     * @authenticated
     * @urlParam id string required The slug or id of the cryptocurrency to unfollow. Example: bitcoin
     *
     * @response 200 {
     *   "message": "Successfully unfollowed cryptocurrency"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 404 {
     *   "message": "No query results for model [App\\Models\\Cryptocurrency]"
     * }
     *
     * @group User Actions
     */
    public function unfollow($id)
    {
        try {
            $user = Auth::user();
            
            // Find the cryptocurrency by ID or slug
            $identifier = request()->input('cryptocurrency', $id);
            $query = Cryptocurrency::query();
            if (is_numeric($identifier)) {
                $query->where('id', (int) $identifier);
            } else {
                $query->where('slug', $identifier);
            }
            $crypto = $query->firstOrFail();
            
            // Detach the cryptocurrency from the user
            $user->cryptocurrencies()->detach($crypto->id);
            
            return response()->json([
                'message' => 'Successfully unfollowed cryptocurrency',
                'cryptocurrency_id' => $crypto->id
            ]);
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Cryptocurrency not found: ' . $id, [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'message' => 'Cryptocurrency not found',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 404);
            
        } catch (\Exception $e) {
            Log::error('Error unfollowing cryptocurrency: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'cryptocurrency_id' => $id,
                'exception' => get_class($e),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ]);
            
            return response()->json([
                'message' => 'Unable to unfollow cryptocurrency',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}
