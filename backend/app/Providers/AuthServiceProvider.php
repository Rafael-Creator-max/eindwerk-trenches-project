<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        // Customize the email verification URL
        VerifyEmail::createUrlUsing(function ($notifiable) {
            // Get the frontend URL from config or use a default
            $frontendUrl = rtrim(config('app.frontend_url', 'http://localhost:3000'), '/');
            
            // Generate the verification URL with a signature
            $parameters = [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ];
            
            // Create a signed URL that expires
            $expires = Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60));
            $parameters['expires'] = $expires->getTimestamp();
            
            // Generate the signature
            $signature = hash_hmac('sha256', http_build_query($parameters), config('app.key'));
            $parameters['signature'] = $signature;
            
            // Build the full URL
            $query = http_build_query($parameters);
            return "{$frontendUrl}/email/verify/{$parameters['id']}/{$parameters['hash']}?{$query}";
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        parent::register();
    }
}
