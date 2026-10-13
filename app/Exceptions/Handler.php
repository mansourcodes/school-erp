<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Auth;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Portal API v1: Arabic messages in the { message } envelope (PLAN.md §7).
        $this->renderable(function (HttpExceptionInterface $e, $request) {
            if (! $request->is('api/v1/*')) {
                return null;
            }

            $messages = [
                403 => 'portal.errors.forbidden',
                404 => 'portal.errors.not_found',
                419 => 'portal.errors.session_expired',
                429 => 'portal.errors.too_many_requests',
            ];
            $status = $e->getStatusCode();
            $key = isset($messages[$status]) ? $messages[$status] : 'portal.errors.server';

            return response()->json(['message' => __($key)], $status, $e->getHeaders());
        });
    }


    protected function unauthenticated($request, AuthenticationException $exception)
    {
        if ($request->is('api/v1/*')) {
            return response()->json(['message' => __('portal.errors.unauthenticated')], 401);
        }
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], 401);
        }
        if ($request->is('student') || $request->is('student/*')) {
            return redirect()->guest('/login/student');
        }

        return redirect()->guest(route('login'));
    }
}
