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

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::get('/login/student',  [App\Http\Controllers\Auth\StudentLoginController::class, 'showStudentLoginForm']);
Route::post('/login/student', [App\Http\Controllers\Auth\StudentLoginController::class, 'studentLogin']);

Route::view('/home', 'home')->middleware('auth');
Route::view('/student', 'student')->middleware('auth');
