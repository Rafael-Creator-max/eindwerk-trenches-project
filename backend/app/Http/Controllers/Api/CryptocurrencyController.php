<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

    public function __construct(CoinGeckoService $coinGeckoService)
    {
        $this->coinGeckoService = $coinGeckoService;
    }

    /**
     * Get List of All Cryptocurrencies
     *
     * Returns a paginated list of all cryptocurrencies ordered by market cap (descending).
     * Data is cached for 5 minutes to improve performance.
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
     *       "current_price": "50000.00000000",
     *       "market_cap": "1000000000000.00",
     *       "volume_24h": "50000000000.00",
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
    public function index()
    {
        // Try to get from cache first
        $cryptos = Cache::remember('cryptocurrencies_list', now()->addMinutes($this->cacheDuration), function () {
            return Cryptocurrency::with('assetType')
                ->orderBy('market_cap', 'desc')
                ->get();
        });

        // If no data in database, try to fetch from API
        if ($cryptos->isEmpty()) {
            $this->fetchAndStoreCryptos();
            $cryptos = Cryptocurrency::with('assetType')
                ->orderBy('market_cap', 'desc')
                ->get();
        }

        return response()->json($cryptos);
    }

    /**
     * Get Cryptocurrency Details
     *
     * Returns detailed information about a specific cryptocurrency by its slug.
     * Data is cached for 5 minutes to improve performance.
     *
     * @urlParam cryptocurrency string required The slug of the cryptocurrency. Example: bitcoin
     *
     * @response 200 {
     *   "id": 1,
     *   "asset_type_id": 1,
     *   "symbol": "BTC",
     *   "name": "Bitcoin",
     *   "slug": "bitcoin",
     *   "external_id": "bitcoin",
     *   "current_price": "50000.00000000",
     *   "market_cap": "1000000000000.00",
     *   "volume_24h": "50000000000.00",
     *   "price_change_24h": "2.50000000",
     *   "created_at": "2023-01-01T00:00:00.000000Z",
     *   "updated_at": "2023-01-01T00:00:00.000000Z",
     *   "asset_type": {
     *     "id": 1,
     *     "name": "Cryptocurrency",
     *     "description": "Digital or virtual currency that uses cryptography for security",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   },
     *   "followers": []
     * }
     *
     * @response 404 {
     *   "message": "Cryptocurrency not found"
     * }
     *
     * @group Cryptocurrency Management
     */
    public function show($cryptocurrency)
    {
        $cacheKey = "crypto_{$cryptocurrency}";
        
        // Try to get from cache first
        $crypto = Cache::remember($cacheKey, now()->addMinutes($this->cacheDuration), function () use ($cryptocurrency) {
            return Cryptocurrency::with('assetType', 'followers')
                ->where('slug', $cryptocurrency)
                ->first();
        });

        // If not found in database, try to fetch from API
        if (!$crypto) {
            $apiData = $this->coinGeckoService->getCoinById($cryptocurrency);
            
            if ($apiData) {
                $crypto = $this->updateOrCreateCryptoFromApi($apiData);
                $crypto->load('assetType', 'followers');
            }
        }

        if (!$crypto) {
            return response()->json(['message' => 'Cryptocurrency not found'], 404);
        }

        return response()->json($crypto);
    }
    
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
    public function follow($cryptocurrency)
    {
        $crypto = Cryptocurrency::where('slug', $cryptocurrency)->firstOrFail();
        
        Auth::user()->cryptocurrencies()->syncWithoutDetaching([$crypto->id]);
        
        return response()->json(['message' => 'Successfully followed cryptocurrency']);
    }

    /**
     * Unfollow a Cryptocurrency
     *
     * Allows the authenticated user to unfollow a specific cryptocurrency.
     * Requires authentication via Bearer token.
     *
     * @authenticated
     * @urlParam cryptocurrency string required The slug of the cryptocurrency to unfollow. Example: bitcoin
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
    public function unfollow($cryptocurrency)
    {
        $crypto = Cryptocurrency::where('slug', $cryptocurrency)->firstOrFail();
        
        Auth::user()->cryptocurrencies()->detach($crypto);
        
        return response()->json(['message' => 'Successfully unfollowed cryptocurrency']);
    }

    /**
     * Get Trending Cryptocurrencies
     *
     * Returns a list of currently trending cryptocurrencies based on CoinGecko's trending data.
     *
     * @response 200 {
     *   "coins": [
     *     {
     *       "item": {
     *         "id": "bitcoin",
     *         "coin_id": 1,
     *         "name": "Bitcoin",
     *         "symbol": "btc",
     *         "market_cap_rank": 1,
     *         "thumb": "https://assets.coingecko.com/coins/images/1/thumb/bitcoin.png",
     *         "small": "https://assets.coingecko.com/coins/images/1/small/bitcoin.png",
     *         "large": "https://assets.coingecko.com/coins/images/1/large/bitcoin.png",
     *         "slug": "bitcoin",
     *         "price_btc": 1,
     *         "score": 0
     *       }
     *     },
     *     ...
     *   ]
     * }
     *
     * @group Cryptocurrency Management
     */
    public function trending()
    {
        $trending = $this->coinGeckoService->getTrendingCoins();
        return response()->json($trending);
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
        $cryptos = Auth::user()->cryptocurrencies()
            ->with('assetType')
            ->get();
            
        return response()->json($cryptos);
    }
}
