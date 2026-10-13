<?php

namespace App\Services\Portal;

use Illuminate\Cache\RateLimiter;
use Illuminate\Contracts\Cache\Repository as Cache;

/**
 * Progressive per-CPR lockout (PLAN.md §6): after N failures for one CPR
 * within the window, that CPR is locked for a fixed time regardless of IP.
 * Unknown CPRs are tracked the same way, so the lockout leaks nothing.
 */
class LoginLockout
{
    protected $limiter;

    protected $cache;

    public function __construct(RateLimiter $limiter, Cache $cache)
    {
        $this->limiter = $limiter;
        $this->cache = $cache;
    }

    /**
     * Seconds until the CPR is unlocked, 0 when it is not locked.
     */
    public function lockedFor($cpr)
    {
        $until = $this->cache->get($this->lockKey($cpr));

        return $until ? max(0, $until - time()) : 0;
    }

    /**
     * Record a failure; returns the attempts left before the lock (0 = just locked).
     */
    public function recordFailure($cpr)
    {
        $max = config('portal.login.lockout_failures');
        $failures = $this->limiter->hit($this->failuresKey($cpr), config('portal.login.lockout_window_seconds'));

        if ($failures >= $max) {
            $seconds = config('portal.login.lockout_seconds');
            $this->cache->put($this->lockKey($cpr), time() + $seconds, $seconds);
            $this->limiter->clear($this->failuresKey($cpr));

            return 0;
        }

        return $max - $failures;
    }

    public function clear($cpr)
    {
        $this->limiter->clear($this->failuresKey($cpr));
    }

    protected function failuresKey($cpr)
    {
        return 'portal-login:failures:' . sha1($cpr);
    }

    protected function lockKey($cpr)
    {
        return 'portal-login:locked:' . sha1($cpr);
    }
}
