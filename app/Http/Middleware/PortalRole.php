<?php

namespace App\Http\Middleware;

use App\Models\Student;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;

/**
 * `portal.role:student` / `portal.role:teacher` on /api/v1 route groups, after auth:sanctum.
 * A teacher session can never reach student endpoints and the other way round.
 */
class PortalRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        $user = $request->user();

        $allowed = $role === 'student'
            ? $user instanceof Student
            : $user instanceof User && $user->hasRole('Teacher');

        abort_unless($allowed, 403);

        return $next($request);
    }
}
