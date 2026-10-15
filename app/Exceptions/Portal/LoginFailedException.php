<?php

namespace App\Exceptions\Portal;

use Exception;

/**
 * One generic answer for every failed login (no user enumeration, PLAN.md §6).
 */
class LoginFailedException extends Exception
{
    protected $attemptsLeft;

    protected $field;

    public function __construct($attemptsLeft, $role = 'student')
    {
        parent::__construct(__($role === 'teacher' ? 'portal.auth.failed_teacher' : 'portal.auth.failed'));
        $this->attemptsLeft = $attemptsLeft;
        $this->field = $role === 'teacher' ? 'username' : 'cpr';
    }

    public function render()
    {
        return response()->json([
            'message' => $this->getMessage(),
            'errors' => [$this->field => [$this->getMessage()]],
            'attempts_left' => $this->attemptsLeft,
        ], 422);
    }
}
