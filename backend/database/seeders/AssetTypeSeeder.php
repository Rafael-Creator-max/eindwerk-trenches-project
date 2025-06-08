<?php

namespace Database\Seeders;

use App\Models\AssetType;
use Illuminate\Database\Seeder;

class AssetTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Cryptocurrency', 'description' => 'Digital or virtual currency that uses cryptography for security'],
            ['name' => 'Token', 'description' => 'Digital asset issued on a blockchain'],
            ['name' => 'Stablecoin', 'description' => 'Cryptocurrency designed to maintain a stable value'],
        ];

        foreach ($types as $type) {
            AssetType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
