<?php

namespace App\Services\Portal;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * A Superadmin opens the teacher portal as one teacher (Backpack → Users → "Open portal").
 * The portal session is the teacher's own (web guard, the teacher's user id), so every teacher endpoint
 * behaves exactly as for that teacher; the session only remembers who is acting behind it.
 * No remember cookie: the impersonation ends with the session, the TTL or the admin's Superadmin role.
 */
class TeacherImpersonation
{
    const IMPERSONATOR = 'portal_impersonator_id';

    const STARTED_AT = 'portal_impersonated_at';

    public function start(User $admin, User $teacher, Request $request)
    {
        // Whoever was signed in to the portal on this device (student or teacher) leaves first. Current
        // device only: a real teacher's other devices keep their remember cookie.
        foreach (PortalLogin::GUARDS as $guard) {
            Auth::guard($guard)->logoutCurrentDevice();
        }

        Auth::guard('web')->login($teacher, false);
        $request->session()->put(self::IMPERSONATOR, $admin->id);
        $request->session()->put(self::STARTED_AT, now()->getTimestamp());

        $this->log('portal.impersonate.start', $admin->id, $teacher->id, $request);
    }

    /** Ends the impersonation: the teacher leaves the portal, the admin's Backpack session stays. */
    public function end(Request $request, $reason = 'logout')
    {
        $adminId = $request->session()->get(self::IMPERSONATOR);
        $teacherId = Auth::guard('web')->id();

        Auth::guard('web')->logoutCurrentDevice();
        $this->forget($request);

        $this->log('portal.impersonate.end', $adminId, $teacherId, $request, ['reason' => $reason]);
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
        $this->log('portal.impersonate.action', $request->session()->get(self::IMPERSONATOR), Auth::guard('web')->id(), $request, [
            'method' => $request->method(),
            'path' => $request->path(),
        ]);
    }

    protected function log($event, $adminId, $teacherId, Request $request, array $extra = [])
    {
        Log::channel('security')->notice($event, [
            'admin_id' => $adminId,
            'teacher_id' => $teacherId,
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
        ] + $extra);
    }
}
