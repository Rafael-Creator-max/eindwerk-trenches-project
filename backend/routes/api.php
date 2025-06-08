<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CryptocurrencyController;

// Public routes
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:api');

// Public cryptocurrency routes
Route::get('cryptocurrencies', [CryptocurrencyController::class, 'index']);
Route::get('cryptocurrencies/{cryptocurrency}', [CryptocurrencyController::class, 'show']);
Route::get('cryptocurrencies/trending', [CryptocurrencyController::class, 'trending']);

// Protected routes (require authentication)
Route::middleware('auth:api')->group(function () {
    // Follow/Unfollow routes
Route::post('cryptocurrencies/{cryptocurrency}/follow', [CryptocurrencyController::class, 'follow']); Route::delete('cryptocurrencies/{cryptocurrency}/follow', [CryptocurrencyController::class, 'unfollow']);
    
// Get user's followed cryptocurrencies
    Route::get('user/cryptocurrencies', [CryptocurrencyController::class, 'followed']);
});
