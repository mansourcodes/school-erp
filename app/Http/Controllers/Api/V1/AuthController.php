<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\V1\MeResource;
use App\Services\Portal\StudentLogin;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request, StudentLogin $login)
    {
        // Cookie sessions only: the SPA must call /sanctum/csrf-cookie from a stateful domain first.
        if (! $request->hasSession()) {
            throw new TokenMismatchException();
        }

        $student = $login->attempt(
            $request->input('cpr'),
            $request->input('password'),
            $request->input('website'),
            $request->input('turnstile_token'),
            $request
        );

        Auth::guard('student')->login($student);
        $request->session()->regenerate();

        return new MeResource($student);
    }

    public function logout(Request $request)
    {
        Auth::guard('student')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
