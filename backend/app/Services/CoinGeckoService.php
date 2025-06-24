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
    /**
     * Get price history for a coin with retry logic
     *
     * @param string $id CoinGecko coin ID
     * @param int $days Number of days of data to fetch (1-365)
     * @param int $maxRetries Maximum number of retry attempts
     * @return array|null Price history data or null on failure
     */
    public function getPriceHistory(string $id, int $days = 7, int $maxRetries = 3)
    {
        $attempt = 0;
        $lastError = null;
        
        // Ensure days is within valid range (1-365)
        $days = max(1, min(365, $days));
        
        // Generate a cache key based on the request
        $cacheKey = "coin_history_{$id}_{$days}";
        $cacheDuration = now()->addMinutes(15); // Cache for 15 minutes
        
        // Try to get from cache first
        if (cache()->has($cacheKey)) {
            $cachedData = cache()->get($cacheKey);
            Log::info('Serving price history from cache', [
                'id' => $id,
                'days' => $days,
                'cache_key' => $cacheKey
            ]);
            return $cachedData;
        }
        
        while ($attempt < $maxRetries) {
            $attempt++;
            
            try {
                // Add delay between retries (increasing with each attempt)
                if ($attempt > 1) {
                    $delay = min(pow(2, $attempt), 10); // Exponential backoff, max 10 seconds
                    Log::warning("Retry attempt {$attempt} for {$id} after {$delay} seconds");
                    sleep($delay);
                }
                
                $this->rateLimit();
                
                $url = self::API_URL . "/coins/" . urlencode($id) . "/market_chart";
                $params = [
                    'vs_currency' => 'usd',
                    'days' => $days,
                    'interval' => 'daily',
                    'precision' => 'full'
                ];
                
                Log::info('CoinGecko API Request:', [
                    'attempt' => $attempt,
                    'id' => $id,
                    'days' => $days,
                    'url' => $url,
                    'params' => $params
                ]);
                
                $response = Http::withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
                ])->timeout(15)->get($url, $params);
                
                $status = $response->status();
                $responseData = $response->json();
                
                Log::debug('CoinGecko API Response:', [
                    'attempt' => $attempt,
                    'status' => $status,
                    'id' => $id,
                    'has_prices' => !empty($responseData['prices']),
                    'prices_count' => is_array($responseData['prices'] ?? null) ? count($responseData['prices']) : 0
                ]);
                
                if ($status === 200 && !empty($responseData['prices'])) {
                    // Validate the price data format
                    $prices = $responseData['prices'];
                    $validPrices = array_filter($prices, function($point) {
                        return is_array($point) && count($point) >= 2 && is_numeric($point[0]) && is_numeric($point[1]);
                    });
                    
                    if (empty($validPrices)) {
                        throw new \Exception('No valid price points found in response');
                    }
                    
                    // Sort by timestamp and ensure unique timestamps
                    usort($validPrices, function($a, $b) {
                        return $a[0] <=> $b[0];
                    });
                    
                    // Remove duplicate timestamps (keep the last occurrence)
                    $uniquePrices = [];
                    $seenTimestamps = [];
                    foreach (array_reverse($validPrices) as $point) {
                        $timestamp = $point[0];
                        if (!isset($seenTimestamps[$timestamp])) {
                            $seenTimestamps[$timestamp] = true;
                            $uniquePrices[] = $point;
                        }
                    }
                    $uniquePrices = array_reverse($uniquePrices);
                    
                    $responseData['prices'] = $uniquePrices;
                    
                    Log::info('Successfully fetched price history', [
                        'id' => $id,
                        'days' => $days,
                        'data_points' => count($uniquePrices),
                        'first_point' => $uniquePrices[0] ?? null,
                        'last_point' => end($uniquePrices) ?: null
                    ]);
                    
                    // Cache the successful response
                    cache()->put($cacheKey, $responseData, $cacheDuration);
                    
                    return $responseData;
                }
                
                // Handle rate limiting (429) or server errors (5xx)
                if (in_array($status, [429, 500, 502, 503, 504])) {
                    $retryAfter = $response->header('Retry-After') ?? 5;
                    Log::warning("Rate limited or server error. Retrying after {$retryAfter} seconds", [
                        'status' => $status,
                        'retry_after' => $retryAfter,
                        'attempt' => $attempt
                    ]);
                    sleep($retryAfter);
                    continue;
                }
                
                // For other client errors, log and return null
                if ($status >= 400 && $status < 500) {
                    $lastError = [
                        'error' => 'Client error',
                        'status' => $status,
                        'response' => $responseData,
                        'attempt' => $attempt
                    ];
                    Log::error('CoinGecko API client error', $lastError);
                    break;
                }
                
            } catch (\Exception $e) {
                $lastError = [
                    'error' => $e->getMessage(),
                    'attempt' => $attempt,
                    'trace' => $e->getTraceAsString()
                ];
                Log::error("Attempt {$attempt} failed for {$id}", $lastError);
                
                // If we've reached max retries, log the final error
                if ($attempt >= $maxRetries) {
                    Log::error("Max retries reached for {$id}", [
                        'error' => $e->getMessage(),
                        'attempts' => $attempt,
                        'days' => $days
                    ]);
                    break;
                }
                
                // Continue to next retry attempt
                continue;
            }
        }
        
        // If we get here, all attempts failed
        Log::error('Failed to fetch price history after all retries', [
            'id' => $id,
            'days' => $days,
            'attempts' => $attempt,
            'last_error' => $lastError
        ]);
        
        return null;
    }


    
}

