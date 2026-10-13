<?php

namespace App\Exceptions\Portal;

use Exception;

/**
 * The CPR is locked after too many failures; the SPA shows the countdown.
 */
class LoginLockedException extends Exception
{
    protected $retryAfter;

    public function __construct($retryAfter)
    {
        parent::__construct(__('portal.auth.locked'));
        $this->retryAfter = $retryAfter;
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'retry_after' => $this->retryAfter,
        ], 429, ['Retry-After' => $this->retryAfter]);
    }
}
