<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CoinGeckoService;
use Illuminate\Support\Facades\Cache;

class UpdateCryptocurrencyData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cryptocurrencies:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update cryptocurrency data from CoinGecko API';

    /**
     * Execute the console command.
     */
    public function handle(CoinGeckoService $coinGeckoService)
    {
        $this->info('Updating cryptocurrency data...');
        
        try {
            // Clear the cache to ensure fresh data is fetched
            Cache::forget('cryptocurrencies_list');
            
            // Fetch and store the latest data
            $coinGeckoService->storeMarketData();
            
            $this->info('Successfully updated cryptocurrency data.');
            
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Failed to update cryptocurrency data: ' . $e->getMessage());
            
            return Command::FAILURE;
        }
    }
}
