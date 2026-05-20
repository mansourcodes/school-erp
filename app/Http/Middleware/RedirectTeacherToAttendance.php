<?php

namespace App\Http\Middleware;

use Closure;

class RedirectTeacherToAttendance
{
    private $allowed = [
        'add_attend_by_date',
        'attend_easy_form',
        'save_attend_easy_form',
        'quick_delete_and_add',
        'edit-account-info',
        'change-password',
    ];

    public function handle($request, Closure $next)
    {
        if (backpack_auth()->check() && backpack_user()->hasRole('Teacher')) {
            $segment = $request->segment(2);
            if (!in_array($segment, $this->allowed)) {
                return redirect(backpack_url('add_attend_by_date'));
            }
        }

        return $next($request);
    }
}
