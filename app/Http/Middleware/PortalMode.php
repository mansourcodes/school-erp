<?php

namespace App\Http\Middleware;

use App\Support\PortalMode as Mode;
use Closure;
use Illuminate\Http\Request;

/**
 * Applies `portal.mode` to the old student portal (`portal.mode:legacy`) and to the
 * new panel's API (`portal.mode:api`). The SPA shell (/beta, or the root once live) always loads, so it can
 * show the maintenance / closed screen; /admin never passes through here.
 */
class PortalMode
{
    public function handle(Request $request, Closure $next, $surface)
    {
        $mode = Mode::current();

        if ($surface === 'api') {
            if ($mode === Mode::MAINTENANCE) {
                return response()->json(
                    ['message' => __('portal.mode.maintenance'), 'code' => 'portal_maintenance'],
                    503,
                    ['Retry-After' => 300]
                );
            }
            if ($mode === Mode::CLOSED) {
                return response()->json(['message' => __('portal.mode.closed'), 'code' => 'portal_closed'], 403);
            }

            return $next($request);
        }

        // Old Blade portal. Once live, / and /login are the new panel's own pages; the rest redirects to it.
        if ($mode === Mode::LIVE) {
            if ($request->isMethod('GET') && ($request->is('/') || $request->is('login'))) {
                return app(\App\Http\Controllers\PortalAppController::class)->shell();
            }

            return redirect(Mode::appPath($request->is('login') ? '/login' : '/'));
        }
        if ($mode === Mode::MAINTENANCE || $mode === Mode::CLOSED) {
            return response()->view('portal.unavailable', ['mode' => $mode], $mode === Mode::MAINTENANCE ? 503 : 403);
        }

        return $next($request);
    }
}
