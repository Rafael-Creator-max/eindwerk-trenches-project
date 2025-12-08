<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */


    'paths' => ['api/*', 'login', 'logout', 'user', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],


    'allowed_origins' => [
        'http://localhost:3000',
        'https://eindwerk-trenches-project-production.up.railway.app',
        'https://your-production-domain.com',
    ],

    'allowed_origins_patterns' => [],


    'allowed_headers' => [
        'Authorization',
        'Content-Type',
        'X-Requested-With',
        'X-XSRF-TOKEN',
        'Accept',
        'X-Socket-Id',
    ],

    'exposed_headers' => ['*'],


    'max_age' => 0,


    'supports_credentials' => true,
];
