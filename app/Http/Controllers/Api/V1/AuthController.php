<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Services\Portal\PortalLogin;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function login(LoginRequest $request, PortalLogin $login)
    {
        // Cookie sessions only: the SPA must call /sanctum/csrf-cookie from a stateful domain first.
        if (! $request->hasSession()) {
            throw new TokenMismatchException();
        }

        $role = $request->input('role');
        $user = $login->attempt(
            $role,
            $role === 'teacher' ? $request->input('email') : $request->input('cpr'),
            $request->input('password'),
            $request->input('website'),
            $request->input('turnstile_token'),
            $request
        );

        Auth::guard(PortalLogin::GUARDS[$role])->login($user);
        $request->session()->regenerate();

        return MeController::resourceFor($user);
    }

    public function logout(Request $request)
    {
        // Either portal guard; the Backpack admin session (its own guard) is not touched.
        foreach (PortalLogin::GUARDS as $guard) {
            Auth::guard($guard)->logout();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
