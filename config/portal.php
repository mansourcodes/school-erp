<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Portal login protection (PLAN.md §6)
    |--------------------------------------------------------------------------
    |
    | Per-request rate limits live in RouteServiceProvider ('student-login').
    | These values drive the progressive per-CPR lockout and the honeypot.
    |
    */

    'login' => [
        // Requests per minute for one (CPR + IP) pair, and for one IP.
        'per_cpr_ip_per_minute' => 5,
        'per_ip_per_minute' => 20,

        // Lock a CPR (regardless of IP) after this many failures in the window.
        'lockout_failures' => 10,
        'lockout_window_seconds' => 15 * 60,
        'lockout_seconds' => 15 * 60,

        // Fixed delay before answering a request that filled the honeypot.
        'honeypot_delay_ms' => (int) env('PORTAL_HONEYPOT_DELAY_MS', 600),
    ],

];
