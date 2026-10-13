<?php

namespace App\Exceptions\Portal;

use Exception;

class BotCheckFailedException extends Exception
{
    public function __construct()
    {
        parent::__construct(__('portal.auth.bot_check_failed'));
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['turnstile_token' => [$this->getMessage()]],
        ], 422);
    }
}
