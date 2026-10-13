<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Stateful Domains
    |--------------------------------------------------------------------------
    |
    | Requests from these domains receive stateful (session cookie) API
    | authentication. The portal SPA is served from the same origin under
    | /beta, so in production this is just the portal host.
    |
    */

    'stateful' => explode(',', env('SANCTUM_STATEFUL_DOMAINS', sprintf(
        '%s%s',
        'localhost,localhost:5173,127.0.0.1,127.0.0.1:8000,::1',
        env('APP_URL') ? ',' . parse_url(env('APP_URL'), PHP_URL_HOST) : ''
    ))),

    /*
    |--------------------------------------------------------------------------
    | Sanctum Guards
    |--------------------------------------------------------------------------
    |
    | Session guards checked by `auth:sanctum`. Students only for now; the
    | teacher phase adds 'web' (PLAN.md §8).
    |
    */

    'guard' => ['student'],

    /*
    |--------------------------------------------------------------------------
    | Expiration Minutes
    |--------------------------------------------------------------------------
    |
    | Only applies to personal access tokens, which the portal does not use.
    |
    */

    'expiration' => null,

    'prefix' => 'sanctum',

    'middleware' => [
        'verify_csrf_token' => App\Http\Middleware\VerifyCsrfToken::class,
        'encrypt_cookies' => App\Http\Middleware\EncryptCookies::class,
    ],

];
