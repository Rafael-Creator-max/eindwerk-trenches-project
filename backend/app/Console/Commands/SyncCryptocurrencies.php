<?php

namespace App\Console\Commands;

use App\Models\AssetType;
use App\Models\Cryptocurrency;
use App\Services\CoinGeckoService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncCryptocurrencies extends Command
{
    protected $signature = 'crypto:sync';
    protected $description = 'Fetch and store cryptocurrency data from CoinGecko';

    protected $coinGeckoService;

    public function __construct(CoinGeckoService $coinGeckoService)
    {
        parent::__construct();
        $this->coinGeckoService = $coinGeckoService;
    }

    public function handle()
    {
        $this->info('Starting cryptocurrency data synchronization...');
        
        try {
            // Get or create the Cryptocurrency asset type
            $assetType = AssetType::firstOrCreate(
                ['name' => 'Cryptocurrency'],
                ['description' => 'Digital or virtual currency that uses cryptography for security']
            );

            // Fetch top 10 cryptocurrencies by market cap
            $cryptos = $this->coinGeckoService->getCoinsMarkets([
                'per_page' => 10, // Limit to 10 coins
                'order' => 'market_cap_desc',
            ]);

            if (empty($cryptos)) {
                $this->error('No cryptocurrency data received from CoinGecko API');
                return Command::FAILURE;
            }

            $updated = 0;
            $created = 0;

            foreach ($cryptos as $cryptoData) {
                $crypto = Cryptocurrency::updateOrCreate(
                    ['external_id' => $cryptoData['id']],
                    [
                        'asset_type_id' => $assetType->id,
                        'symbol' => strtoupper($cryptoData['symbol']),
                        'name' => $cryptoData['name'],
                        'slug' => $cryptoData['id'],
                        'current_price' => $cryptoData['current_price'],
                        'market_cap' => $cryptoData['market_cap'],
                        'volume_24h' => $cryptoData['total_volume'],
                        'price_change_24h' => $cryptoData['price_change_percentage_24h_in_currency'] ?? 0,
                    ]
                );

                $crypto->wasRecentlyCreated ? $created++ : $updated++;
            }

            $this->info("Synchronization complete! Created: {$created}, Updated: {$updated}");
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $errorMsg = 'Error in crypto:sync command: ' . $e->getMessage();
            $this->error($errorMsg);
            Log::error($errorMsg, ['exception' => $e]);
            return Command::FAILURE;
        }
    }
}
