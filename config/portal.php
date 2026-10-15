<?php

return [

    /*
    | The centre's local time, for "today" and "has this session started" (the app
    | itself runs in UTC, see config/app.php).
    */
    'timezone' => env('PORTAL_TIMEZONE', 'Asia/Bahrain'),

    /*
    | Reverse proxies whose X-Forwarded-* headers are trusted (PLAN.md §10.5): comma list of IPs/CIDRs.
    | "cloudflare" expands to the ranges below; use 127.0.0.1 behind a Cloudflare Tunnel (cloudflared).
    | Without this, every request looks like it comes from the proxy, and per-IP login limits break.
    */
    'trusted_proxies' => env('TRUSTED_PROXIES'),

    // https://www.cloudflare.com/ips/ — check again before deploying (the list changes rarely).
    'cloudflare_ips' => [
        '173.245.48.0/20', '103.21.244.0/22', '103.22.200.0/22', '103.31.4.0/22', '141.101.64.0/18',
        '108.162.192.0/18', '190.93.240.0/20', '188.114.96.0/20', '197.234.240.0/22', '198.41.128.0/17',
        '162.158.0.0/15', '104.16.0.0/13', '104.24.0.0/14', '172.64.0.0/13', '131.0.72.0/22',
        '2400:cb00::/32', '2606:4700::/32', '2803:f800::/32', '2405:b500::/32', '2405:8100::/32',
        '2a06:98c0::/29', '2c0f:f248::/32',
    ],

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

    /*
    | A Superadmin opening the teacher portal as a teacher (Backpack → Users → "Open portal"):
    | the impersonation signs out after this many minutes.
    */
    'impersonation' => [
        'ttl_minutes' => (int) env('PORTAL_IMPERSONATION_TTL_MINUTES', 60),
    ],

];
