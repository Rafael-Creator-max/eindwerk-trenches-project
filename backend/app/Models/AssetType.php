<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssetType extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    public function cryptocurrencies()
    {
        return $this->hasMany(Cryptocurrency::class);
    }
}
