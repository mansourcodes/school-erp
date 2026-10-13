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

    Route::middleware('auth:student')->group(function () {
        Route::get('/', [\App\Http\Controllers\StudentPanel\StudentPanelController::class, 'dashboard'])->name('dashboard');
        Route::get('/dashboard', [\App\Http\Controllers\StudentPanel\StudentPanelController::class, 'dashboard'])->name('dashboard');
    });
});
Route::post('/logout', [\App\Http\Controllers\StudentPanel\StudentAuthController::class, 'logout'])->name('logout');

// New student panel (React SPA, built into public/beta). Hashed assets and sw.js are static files;
// every other /beta path is a client-side route, so it gets the app shell.
Route::get('/beta/{path?}', function () {
    $index = public_path('beta/index.html');
    abort_unless(is_file($index), 404);

    return response()->file($index, ['Cache-Control' => 'no-cache', 'Content-Type' => 'text/html; charset=UTF-8']);
})->where('path', '.*')->name('beta');
