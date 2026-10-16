<?php

namespace App\Services\Portal;

use App\Http\Middleware\PortalAuthenticateSession;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A Superadmin opens the portal as a student or a teacher (Backpack → Students / Users → "Open portal").
 * The portal session is the user's own (their guard and id), so every endpoint behaves exactly as for
 * them; the session only remembers who is acting behind it.
 * No remember cookie: the impersonation ends with the session, the TTL or the admin's Superadmin role.
 */
class PortalImpersonator
{
    const IMPERSONATOR = 'portal_impersonator_id';

    const STARTED_AT = 'portal_impersonated_at';

    /**
     * @param  Student|User  $user
     * @param  string  $role  student | teacher
     */
    public function start(User $admin, $user, $role, Request $request)
    {
        // Whoever was signed in to the portal on this device leaves first. Current device only: the
        // user's other devices keep their remember cookie.
        $this->logoutPortal();

        Auth::guard(PortalLogin::GUARDS[$role])->login($user, false);
        // Replaces the previous portal user's password hash; left alone, it signed this user out on the first
        // request and rotated the CSRF token, so the next "Open portal" click in Backpack answered 419.
        PortalAuthenticateSession::remember($request, PortalLogin::GUARDS[$role]);
        $request->session()->put(self::IMPERSONATOR, $admin->id);
        $request->session()->put(self::STARTED_AT, now()->getTimestamp());

        $this->log('portal.impersonate.start', $admin->id, $role . ':' . $user->id, $request);
    }

    /** Ends the impersonation: the user leaves the portal, the admin's Backpack session stays. */
    public function end(Request $request, $reason = 'logout')
    {
        $adminId = $request->session()->get(self::IMPERSONATOR);
        $actingAs = $this->actingAs($request);

        $this->logoutPortal();
        $this->forget($request);

        $this->log('portal.impersonate.end', $adminId, $actingAs, $request, ['reason' => $reason]);
    }

    public function forget(Request $request)
    {
        $request->session()->forget([self::IMPERSONATOR, self::STARTED_AT]);
    }

    public function active(Request $request)
    {
        return $request->hasSession() && $request->session()->has(self::IMPERSONATOR);
    }

    /** The acting Superadmin, or null when the portal user signed in themselves. */
    public function impersonator(Request $request)
    {
        return $this->active($request) ? User::find($request->session()->get(self::IMPERSONATOR)) : null;
    }

    /** Still a Superadmin, and within config('portal.impersonation.ttl_minutes') of the start. */
    public function valid(Request $request)
    {
        $admin = $this->impersonator($request);
        $startedAt = (int) $request->session()->get(self::STARTED_AT);
        $ttl = (int) config('portal.impersonation.ttl_minutes') * 60;

        return $admin && $admin->hasRole('Superadmin') && now()->getTimestamp() - $startedAt < $ttl;
    }

    public function logAction(Request $request)
    {
        $this->log('portal.impersonate.action', $request->session()->get(self::IMPERSONATOR), $this->actingAs($request), $request, [
            'method' => $request->method(),
            'path' => $request->path(),
        ]);
    }

    protected function logoutPortal()
    {
        foreach (PortalLogin::GUARDS as $guard) {
            Auth::guard($guard)->logoutCurrentDevice();
        }
    }

    /** "student:12" / "teacher:5", for the log. */
    protected function actingAs(Request $request)
    {
        foreach (PortalLogin::GUARDS as $role => $guard) {
            if (Auth::guard($guard)->check()) {
                return $role . ':' . Auth::guard($guard)->id();
            }
        }

        return null;
    }

    protected function log($event, $adminId, $actingAs, Request $request, array $extra = [])
    {
        Log::channel('security')->notice($event, [
            'admin_id' => $adminId,
            'acting_as' => $actingAs,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
        ] + $extra);
    }
}
