<?php

namespace App\Http\Middleware;

use App\Services\Portal\PortalImpersonator;
use Closure;
use Illuminate\Http\Request;

/**
 * `portal.impersonation` on the /api/v1 auth group, after auth:sanctum: an expired impersonation
 * (TTL, or the admin lost the Superadmin role) signs the student / teacher out with a 401; changes made while
 * impersonating go to the security log.
 * `portal.impersonation:deny` on the user's own account endpoints (password, username, own photo, push
 * subscriptions, notification settings): an admin manages those in Backpack, and this device is not theirs.
 */
class PortalImpersonation
{
    protected $impersonation;

    public function __construct(PortalImpersonator $impersonation)
    {
        $this->impersonation = $impersonation;
    }

    public function handle(Request $request, Closure $next, $mode = null)
    {
        if (! $this->impersonation->active($request)) {
            return $next($request);
        }

        if ($mode === 'deny') {
            abort(403);
        }

        if (! $this->impersonation->valid($request)) {
            $this->impersonation->end($request, 'expired');
            abort(401);
        }

        if (! $request->isMethodSafe()) {
            $this->impersonation->logAction($request);
        }

        return $next($request);
    }
}
