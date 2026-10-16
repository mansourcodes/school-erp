<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Current Blade student portal. portal.mode (Backpack Settings) can redirect it to the new
// panel (live) or replace it with a notice (maintenance / closed), PLAN.md §4.
Route::middleware('portal.mode:legacy')->group(function () {
    Route::get('/login', [\App\Http\Controllers\StudentPanel\StudentAuthController::class, 'showLoginForm'])->name('login');
    // Same per-(CPR + IP) and per-IP limits as the new login (PLAN.md §4 "Sessions between the two portals").
    Route::post('/login', [\App\Http\Controllers\StudentPanel\StudentAuthController::class, 'login'])
        ->middleware('throttle:legacy-student-login');

    Route::middleware(['auth:student', 'portal.session'])->group(function () {
        Route::get('/', [\App\Http\Controllers\StudentPanel\StudentPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\StudentPanel\StudentPanelController::class, 'dashboard'])->name('dashboard');
    });
});
Route::post('/logout', [\App\Http\Controllers\StudentPanel\StudentAuthController::class, 'logout'])->name('logout');

// New student panel (React SPA, built into public/beta): the manifest and the app shell carry the
// Backpack Settings texts (PortalBranding). Pages are at /beta until portal.mode is live, then at the
// root: /beta redirects there, unmatched GET paths get the shell, and the worker is served at /sw.js.
Route::get('/beta/manifest.webmanifest', [\App\Http\Controllers\PortalAppController::class, 'manifest'])->name('beta.manifest');
Route::get('/beta/{path?}', [\App\Http\Controllers\PortalAppController::class, 'beta'])->where('path', '.*')->name('beta');
Route::get('/sw.js', [\App\Http\Controllers\PortalAppController::class, 'serviceWorker'])->name('portal.sw');
Route::fallback([\App\Http\Controllers\PortalAppController::class, 'fallback']);
