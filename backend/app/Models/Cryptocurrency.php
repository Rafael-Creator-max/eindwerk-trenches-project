<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cryptocurrency extends Model
{
    protected $fillable = [
        'asset_type_id',
        'symbol',
        'name',
        'slug',
        'external_id',
        'current_price',
        'market_cap',
        'volume_24h',
        'price_change_24h',
        'image_url'
    ];

    protected $casts = [
        'current_price' => 'decimal:8',
        'market_cap' => 'decimal:2',
        'volume_24h' => 'decimal:2',
        'price_change_24h' => 'decimal:8'
    ];

    public function assetType()
    {
        return $this->belongsTo(AssetType::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_crypto_follows');
    }
}
