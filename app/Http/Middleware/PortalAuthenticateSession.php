<?php

namespace App\Http\Middleware;

use App\Services\Portal\PortalImpersonator;
use App\Services\Portal\PortalLogin;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * `portal.session`, after the portal's auth middleware: a new password ends the user's other live sessions,
 * not only their remember cookies (SignsOutDevicesOnPasswordChange). Laravel 8's AuthenticateSession only
 * checks the default guard, so this one checks whichever portal guard signed the request in.
 * The Backpack admin session (its own guard) is never touched, also while an admin is impersonating.
 */
class PortalAuthenticateSession
{
    protected $impersonation;

    public function __construct(PortalImpersonator $impersonation)
    {
        $this->impersonation = $impersonation;
    }

    public function handle(Request $request, Closure $next)
    {
        $guard = $this->guardFor($request);
        if (! $guard) {
            return $next($request);
        }

        $stored = $request->session()->get(static::key($guard));
        if ($stored === null) {
            // A session from before this check, or restored by the remember cookie: start from now.
            static::remember($request, $guard);
        } elseif (! hash_equals($stored, (string) Auth::guard($guard)->user()->getAuthPassword())) {
            return $this->signOut($request, $guard);
        }

        return tap($next($request), function () use ($request) {
            // The password may have changed on this request (own password change): keep this device in.
            if ($guard = $this->guardFor($request)) {
                static::remember($request, $guard);
            }
        });
    }

    /** Stores the signed-in user's current password hash for this session; AuthController::login calls it too. */
    public static function remember(Request $request, $guard)
    {
        $request->session()->put(static::key($guard), (string) Auth::guard($guard)->user()->getAuthPassword());
    }

    protected static function key($guard)
    {
        return 'portal_password_hash_' . $guard;
    }

    /** The portal guard holding this request's session login, or null (no session, or a token request). */
    protected function guardFor(Request $request)
    {
        if (! $request->hasSession()) {
            return null;
        }

        foreach (PortalLogin::GUARDS as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }

        return null;
    }

    protected function signOut(Request $request, $guard)
    {
        if ($this->impersonation->active($request)) {
            $this->impersonation->end($request, 'password_changed');
        } else {
            Auth::guard($guard)->logoutCurrentDevice();
        }
        $request->session()->forget(static::key($guard));
        $request->session()->regenerateToken();

        if (! $request->expectsJson()) {
            return redirect()->route('login');
        }

        abort(401);
    }
}
