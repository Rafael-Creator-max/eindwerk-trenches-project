<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCryptoFollow extends Model
{
    protected $table = 'user_crypto_follows';

    protected $fillable = [
        'user_id',
        'cryptocurrency_id'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cryptocurrency()
    {
        return $this->belongsTo(Cryptocurrency::class);
    }
}
