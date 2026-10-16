<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * `guest:backpack` on Backpack's login / password pages: a signed-in admin goes to the dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  ...$guards
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        $guards = empty($guards) ? [null] : $guards;

        foreach ($guards as $guard) {
            if (is_null($guard) || ! Auth::guard($guard)->check()) {
                continue;
            }
            if ($guard == "student") {
                return redirect('/student');
            }
            if ($guard === backpack_guard_name()) {
                return redirect(backpack_url('dashboard'));
            }

            return redirect(RouteServiceProvider::HOME);
        }

        return $next($request);
    }
}
