<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to the "home" route for your application.
     *
     * This is used by Laravel authentication to redirect users after login.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * The controller namespace for the application.
     *
     * When present, controller route declarations will automatically be prefixed with this namespace.
     *
     * @var string|null
     */
    // protected $namespace = 'App\\Http\\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::prefix('api')
                ->middleware('api')
                ->namespace($this->namespace)
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->namespace($this->namespace)
                ->group(base_path('routes/web.php'));
        });
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            // The portal is used from classroom Wi-Fi: many students share one IP, and this
            // runs before the session is read. So v1 gets a generous per-IP ceiling here and
            // a per-student limit ('portal') after authentication (PLAN.md §9.2, §10.3).
            if ($request->is('api/v1/*')) {
                return Limit::perMinute(600)->by('v1|' . $request->ip());
            }

            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // The old Blade login form: same limits, answered the Blade way.
        RateLimiter::for('legacy-student-login', function (Request $request) {
            $tooMany = function (Request $request) {
                return back()->withInput($request->only('cpr'))
                    ->withErrors(['cpr' => __('localize.too_many_login_attempts')]);
            };

            return [
                Limit::perMinute(config('portal.login.per_cpr_ip_per_minute'))
                    ->by('legacy-cpr-ip|' . sha1((string) $request->input('cpr')) . '|' . $request->ip())
                    ->response($tooMany),
                Limit::perMinute(config('portal.login.per_ip_per_minute'))
                    ->by('legacy-ip|' . $request->ip())
                    ->response($tooMany),
            ];
        });

        // Photo uploads (PLAN.md §5.8): 5 an hour per teacher and student, so a class photo day still works.
        RateLimiter::for('student-photo', function (Request $request) {
            return Limit::perHour(5)->by('photo|' . $request->user()->getAuthIdentifier() . '|' . $request->route('sid'));
        });

        // The teacher's own photo: 5 an hour.
        RateLimiter::for('teacher-photo', function (Request $request) {
            return Limit::perHour(5)->by('teacher-photo|' . $request->user()->getAuthIdentifier());
        });

        RateLimiter::for('portal',function (Request $request) {
            return Limit::perMinute(120)->by('portal|' . get_class($request->user()) . '|' . $request->user()->getAuthIdentifier());
        });

        // Portal login (PLAN.md §6): per (account + IP) and per IP; account = CPR or teacher email.
        // The per-account lockout is in LoginLockout.
        RateLimiter::for('student-login', function (Request $request) {
            $tooMany = function (Request $request, array $headers) {
                return response()->json([
                    'message' => __('portal.auth.throttled'),
                    'retry_after' => (int) $headers['Retry-After'],
                ], 429, $headers);
            };

            return [
                Limit::perMinute(config('portal.login.per_cpr_ip_per_minute'))
                    ->by('account-ip|' . sha1($request->input('role') . '|' . $request->input('cpr') . '|' . strtolower((string) $request->input('email'))) . '|' . $request->ip())
                    ->response($tooMany),
                Limit::perMinute(config('portal.login.per_ip_per_minute'))
                    ->by('ip|' . $request->ip())
                    ->response($tooMany),
            ];
        });
    }
}
