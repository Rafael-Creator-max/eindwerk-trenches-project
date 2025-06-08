<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
}
