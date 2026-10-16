<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Middleware\PortalAuthenticateSession;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Services\Portal\PortalLogin;
use App\Services\Portal\PortalImpersonator;
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
            $role === 'teacher' ? $request->input('username') : $request->input('cpr'),
            $request->input('password'),
            $request->input('website'),
            $request->input('turnstile_token'),
            $request
        );

        // A shared phone: whoever was signed in to the other portal guard leaves first, or Sanctum (student guard
        // first) would keep answering as them. Current device only: their other devices keep their remember cookie.
        foreach (PortalLogin::GUARDS as $other => $guard) {
            if ($other !== $role) {
                Auth::guard($guard)->logoutCurrentDevice();
            }
        }

        // Remember-me cookie: the portal stays signed in until the user logs out, even after the
        // session itself expires (SESSION_LIFETIME). A password change signs every device out.
        Auth::guard(PortalLogin::GUARDS[$role])->login($user, true);
        // A real sign-in on a device an admin was impersonating on: it is the user's own session now.
        app(PortalImpersonator::class)->forget($request);
        $request->session()->regenerate();
        PortalAuthenticateSession::remember($request, PortalLogin::GUARDS[$role]);

        return MeController::resourceFor($user);
    }

    public function logout(Request $request, PortalImpersonator $impersonation)
    {
        // An admin ending an impersonation: only the student / teacher leaves; the admin stays signed in to Backpack.
        if ($impersonation->active($request)) {
            $impersonation->end($request);
            $request->session()->regenerateToken();

            return response()->noContent();
        }

        // Either portal guard; the Backpack admin session (its own guard) is not touched. This device
        // only: it forgets its remember cookie; the user's other devices stay signed in.
        foreach (PortalLogin::GUARDS as $guard) {
            Auth::guard($guard)->logoutCurrentDevice();
        }
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
