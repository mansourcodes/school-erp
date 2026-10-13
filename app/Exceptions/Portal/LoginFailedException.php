<?php

namespace App\Exceptions\Portal;

use Exception;

/**
 * One generic answer for every failed login (no user enumeration, PLAN.md §6).
 */
class LoginFailedException extends Exception
{
    protected $attemptsLeft;

    public function __construct($attemptsLeft)
    {
        parent::__construct(__('portal.auth.failed'));
        $this->attemptsLeft = $attemptsLeft;
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => ['cpr' => [$this->getMessage()]],
            'attempts_left' => $this->attemptsLeft,
        ], 422);
    }
}
