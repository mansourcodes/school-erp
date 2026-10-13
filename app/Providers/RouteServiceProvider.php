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
            return Limit::perMinute(60)->by(optional($request->user())->id ?: $request->ip());
        });

        // Portal login (PLAN.md §6): per (CPR + IP) and per IP. The per-CPR lockout is in LoginLockout.
        RateLimiter::for('student-login', function (Request $request) {
            $tooMany = function (Request $request, array $headers) {
                return response()->json([
                    'message' => __('portal.auth.throttled'),
                    'retry_after' => (int) $headers['Retry-After'],
                ], 429, $headers);
            };

            return [
                Limit::perMinute(config('portal.login.per_cpr_ip_per_minute'))
                    ->by('cpr-ip|' . sha1((string) $request->input('cpr')) . '|' . $request->ip())
                    ->response($tooMany),
                Limit::perMinute(config('portal.login.per_ip_per_minute'))
                    ->by('ip|' . $request->ip())
                    ->response($tooMany),
            ];
        });
    }
}
