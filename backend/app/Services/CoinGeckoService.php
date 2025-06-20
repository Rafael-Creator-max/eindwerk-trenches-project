<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Cryptocurrency;

class CoinGeckoService
{
    private const API_URL = 'https://api.coingecko.com/api/v3';
    private const RATE_LIMIT_REQUESTS = 30; // Requests per minute
    private const RATE_LIMIT_INTERVAL = 60; // Seconds
    private $lastRequestTime = null;

    /**
     * Get list of coins with market data
     *
     * @param array $params
     * @return array|null
     */
    public function getCoinsMarkets(array $params = [])
    {
        $defaultParams = [
            'vs_currency' => 'usd',
            'order' => 'market_cap_desc',
            'per_page' => 10, // Limit to 10 still on the free plan
            'page' => 1,
            'sparkline' => false,
            'price_change_percentage' => '24h',
        ];

        $params = array_merge($defaultParams, $params);

        try {
            $this->rateLimit();
            $response = Http::get(self::API_URL . '/coins/markets', $params);
            
            if ($response->successful()) {
                return $response->json();
            }

            Log::error('CoinGecko API error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);
            return null;

        } catch (\Exception $e) {
            Log::error('Failed to fetch coins market data: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get detailed information about a specific coin
     * 
     * @param string $id
     * @return array|null
     */
    public function getCoinById(string $id)
    {
        try {
            $this->rateLimit();
            $response = Http::get(self::API_URL . "/coins/{$id}");
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch coin {$id}: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Get trending coins
     * 
     * @return array|null
     */
    public function getTrendingCoins()
    {
        try {
            $this->rateLimit();
            $response = Http::get(self::API_URL . '/search/trending');
            
            if ($response->successful()) {
                return $response->json();
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error('Failed to fetch trending coins: ' . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Simple rate limiting to prevent hitting CoinGecko API limits
     */
    private function rateLimit()
    {
        if ($this->lastRequestTime) {
            $elapsed = microtime(true) - $this->lastRequestTime;
            $minInterval = self::RATE_LIMIT_INTERVAL / self::RATE_LIMIT_REQUESTS;
            
            if ($elapsed < $minInterval) {
                $sleepTime = ($minInterval - $elapsed) * 1000000; // Convert to microseconds
                usleep($sleepTime);
            }
        }
        
        $this->lastRequestTime = microtime(true);
    }
/**
 * Store market data in the db
 * 
 */
    public function storeMarketData()
    {
        $coins = $this->getCoinsMarkets();

        if (!$coins) {
            Log::warning('No coin market data returned from CoinGecko');
            return;
        }

        foreach ($coins as $coin) {
            try {
                // Get additional details for the coin
                $coinDetails = $this->getCoinById($coin['id']);
                
                // Try to get the best available image URL
                $imageUrl = null;
                
                // 1. Try to get from coin details (large image)
                if ($coinDetails && !empty($coinDetails['image']['large'])) {
                    $imageUrl = $coinDetails['image']['large'];
                } 
                // 2. Try to get from coin details (small image)
                elseif ($coinDetails && !empty($coinDetails['image']['small'])) {
                    $imageUrl = $coinDetails['image']['small'];
                }
                // 3. Try to get from market data
                elseif (!empty($coin['image'])) {
                    $imageUrl = $coin['image'];
                }
                // 4. Construct URL using coin ID as fallback
                if (empty($imageUrl)) {
                    // Try common URL patterns for the image
                    $commonPatterns = [
                        "https://assets.coingecko.com/coins/images/1/large/{$coin['id']}.png",
                        "https://assets.coingecko.com/coins/images/1/small/{$coin['id']}.png",
                        "https://cryptologos.cc/logos/{$coin['id']}-{$coin['symbol']}-logo.png",
                        "https://cryptoicons.org/api/icon/{$coin['symbol']}/200"
                    ];
                    
                    // Try to find the first working URL
                    foreach ($commonPatterns as $pattern) {
                        if ($this->checkImageUrl($pattern)) {
                            $imageUrl = $pattern;
                            break;
                        }
                    }
                    
                    // If still no URL, use a placeholder service
                    if (empty($imageUrl)) {
                        $imageUrl = "https://cryptologos.cc/logos/{$coin['id']}-{$coin['symbol']}-logo.png";
                    }
                }
                
                // Ensure the URL is complete (add protocol if missing)
                if ($imageUrl && strpos($imageUrl, 'http') !== 0) {
                    $imageUrl = 'https:' . ltrim($imageUrl, ':');
                }
                
                // Clean up the URL
                $imageUrl = filter_var($imageUrl, FILTER_SANITIZE_URL);

                Cryptocurrency::updateOrCreate(
                    ['external_id' => $coin['id']],
                    [
                        'symbol' => $coin['symbol'],
                        'name' => $coin['name'],
                        'slug' => $coin['id'],
                        'current_price' => $coin['current_price'],
                        'market_cap' => $coin['market_cap'],
                        'volume_24h' => $coin['total_volume'],
                        'price_change_24h' => $coin['price_change_percentage_24h'] ?? 0,
                        'image_url' => $imageUrl,
                        'asset_type_id' => 1,
                    ]
                );
                
                Log::info("Updated {$coin['name']} with image URL: {$imageUrl}");
                
            } catch (\Exception $e) {
                Log::error("Failed to update {$coin['name']} ({$coin['id']}): " . $e->getMessage());
                continue;
            }
        }
    }

    /**
     * Check if an image URL exists
     *
     * @param string $url
     * @return bool
     */
    private function checkImageUrl(string $url): bool
    {
        try {
            $client = new \GuzzleHttp\Client(['timeout' => 5]);
            $response = $client->head($url, ['http_errors' => false]);
            return $response->getStatusCode() === 200;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Get price history for a coin
     *
     * @param string $id
     * @param int $days
     * @return array|null
     */
    public function getPriceHistory(string $id, int $days = 7)
    {
        try {
            $this->rateLimit();
            
            $url = self::API_URL . "/coins/" . urlencode($id) . "/market_chart";
            $params = [
                'vs_currency' => 'usd',
                'days' => $days,
                'interval' => 'daily',
                'precision' => 'full'
            ];
            
            Log::debug('CoinGecko API Request:', [
                'url' => $url,
                'params' => $params,
                'id' => $id
            ]);
            
            $response = Http::withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
            ])->timeout(15)->get($url, $params);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (empty($data) || !isset($data['prices']) || !is_array($data['prices'])) {
                    Log::error('Invalid response format from CoinGecko API', [
                        'status' => $response->status(),
                        'response' => $data,
                        'id' => $id
                    ]);
                    return null;
                }
                
                // Ensure we have valid data points
                if (empty($data['prices'])) {
                    Log::warning('Empty price data from CoinGecko API', [
                        'id' => $id,
                        'days' => $days
                    ]);
                }
                
                return $data;
            }
            
            Log::error('CoinGecko API error', [
                'status' => $response->status(),
                'response' => $response->body(),
                'id' => $id,
                'url' => $url
            ]);
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Failed to fetch price history for {$id}", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'id' => $id,
                'days' => $days
            ]);
            return null;
        }
    }


    
}

