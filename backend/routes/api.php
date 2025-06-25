<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CryptocurrencyController;
use App\Http\Controllers\Api\TokenAuthController;

// Authentication routes
Route::post('/register', [TokenAuthController::class, 'register']);
Route::post('/login', [TokenAuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [TokenAuthController::class, 'logout']);
Route::middleware('auth:sanctum')->get('/user', [TokenAuthController::class, 'user']);

// Public cryptocurrency routes
Route::get('cryptocurrencies', [CryptocurrencyController::class, 'index']);
Route::get('cryptocurrencies/{id}', [CryptocurrencyController::class, 'show']);
// Both endpoints point to the same controller method for backward compatibility
Route::get('cryptocurrencies/{id}/chart', [CryptocurrencyController::class, 'priceHistory']);
Route::get('cryptocurrencies/{id}/price-history', [CryptocurrencyController::class, 'priceHistory']);
Route::get('cryptocurrencies/trending', [CryptocurrencyController::class, 'trending']);

// Protected routes (require authentication)
Route::middleware('auth:sanctum')->group(function () {
    // Follow/Unfollow routes
    Route::post('cryptocurrencies/{id}/follow', [CryptocurrencyController::class, 'follow']);
    Route::delete('cryptocurrencies/{id}/follow', [CryptocurrencyController::class, 'unfollow']);
    
    // Get user's followed cryptocurrencies
    Route::get('user/cryptocurrencies', [CryptocurrencyController::class, 'followed']);
});

