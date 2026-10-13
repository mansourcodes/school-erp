<?php

namespace App\Services\Portal;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Server-side check of a Cloudflare Turnstile token (PLAN.md §6).
 * Disabled when no secret is configured, e.g. in local development.
 */
class TurnstileVerifier
{
    public function enabled()
    {
        return (bool) config('services.turnstile.secret');
    }

    /**
     * Fails closed: a missing token or an unreachable siteverify endpoint
     * both count as a failed check.
     */
    public function verify($token, $ip)
    {
        if (! $this->enabled()) {
            return true;
        }

        if (! is_string($token) || $token === '') {
            return false;
        }

        try {
            $response = Http::asForm()->timeout(5)->post(config('services.turnstile.verify_url'), [
                'secret' => config('services.turnstile.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);
        } catch (Throwable $e) {
            Log::channel('security')->warning('portal.turnstile.unreachable', ['error' => $e->getMessage()]);

            return false;
        }

        return $response->ok() && $response->json('success') === true;
    }
}
