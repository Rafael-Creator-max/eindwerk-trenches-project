<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin/login');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

/**
 *  for testing
 * 
 *  */ 
Route::get('/fetch-and-store-cryptos', function () {
    (new \App\Services\CoinGeckoService)->storeMarketData();
    return 'Synced!';
});