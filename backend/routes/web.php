<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

// Email Verification Routes
Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->middleware('auth')->name('verification.notice');

// Email verification handler - custom implementation to handle verification
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    try {
        // Find the user by ID
        $user = \App\Models\User::findOrFail($id);
        
        // Verify the hash
        if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            throw new \Exception('Invalid verification hash');
        }
        
        // Check if the signature is valid
        if (! $request->hasValidSignature()) {
            throw new \Exception('Invalid or expired signature');
        }
        
        // Check if the link has expired
        if (($expires = $request->query('expires')) && Carbon::now()->getTimestamp() > $expires) {
            throw new \Exception('Verification link has expired');
        }
        
        // Mark the email as verified
        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            \Log::info('Email verified successfully', ['user_id' => $user->id]);
        }
        
        // For API requests, return JSON response
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Email verified successfully']);
        }
        
        // For web requests, redirect to the login page with success message
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        return redirect("{$frontendUrl}/login?verified=1&email=" . urlencode($user->email));
        
    } catch (\Exception $e) {
        \Log::error('Email verification failed', [
            'error' => $e->getMessage(),
            'user_id' => $id,
            'hash' => $hash
        ]);
        
        // For API requests, return error response
        if ($request->wantsJson()) {
            return response()->json([
                'error' => $e->getMessage(),
                'message' => 'Email verification failed'
            ], 403);
        }
        
        // For web requests, redirect to the frontend with error message
        $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
        return redirect("{$frontendUrl}/email-verification-error?error=" . urlencode($e->getMessage()));
    }
})->name('verification.verify');

// Resend verification email
Route::post('/email/verification-notification', function (Request $request) {
    try {
        $request->user()->sendEmailVerificationNotification();
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Verification link sent!']);
        }
        
        return back()->with('message', 'Verification link sent!');
    } catch (\Exception $e) {
        \Log::error('Failed to resend verification email', [
            'user_id' => $request->user()->id,
            'error' => $e->getMessage()
        ]);
        
        if ($request->wantsJson()) {
            return response()->json(['error' => 'Failed to send verification email. Please try again.'], 500);
        }
        
        return back()->with('error', 'Failed to send verification email. Please try again.');
    }
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// Default route
Route::get('/', function () {
    return redirect('/admin/login');
});

// Protected routes
Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Test route for syncing crypto data
Route::get('/fetch-and-store-cryptos', function () {
    // Allow longer execution for heavy syncs
    @set_time_limit(300);
    @ini_set('max_execution_time', '300');

    (new \App\Services\CoinGeckoService)->storeMarketData();
    return 'Synced!';
});

// Test email route
Route::get('/test-email', function () {
    try {
        // Send a test email to the authenticated user or a default email
        $email = auth()->check() ? auth()->user()->email : 'test@example.com';
        
        \Illuminate\Support\Facades\Mail::raw('This is a test email from Crypto Tracker', function($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email from Crypto Tracker');
        });
        
        return 'Test email sent to ' . $email;
    } catch (\Exception $e) {
        return 'Error sending email: ' . $e->getMessage();
    }
});

Route::get('/run-migrate', function () {
    try {
        Artisan::call('migrate', ['--force' => true]);

        return nl2br(Artisan::output());
    } catch (\Throwable $e) {
        return response(
            nl2br(
                $e->getMessage()
                . "\n\nin " . $e->getFile()
                . ":" . $e->getLine()
            ),
            500
        );
    }
});

Route::get('/run-scribe', function () {
    try {
        Artisan::call('scribe:generate');

        return nl2br(Artisan::output());
    } catch (\Throwable $e) {
        return response(
            nl2br(
                $e->getMessage()
                . "\n\nin " . $e->getFile()
                . ":" . $e->getLine()
            ),
            500
        );
    }
});

Route::get('/run-seed', function () {
    try {
        Artisan::call('db:seed', ['--force' => true]);

        return nl2br(Artisan::output());
    } catch (\Throwable $e) {
        return response(
            nl2br(
                $e->getMessage()
                . "\n\nin " . $e->getFile()
                . ":" . $e->getLine()
            ),
            500
        );
    }
});