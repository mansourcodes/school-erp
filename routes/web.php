<?php

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

Route::get('/', function () {
    return redirect('/admin');
});


Route::get('/pdf/test2', function () {
    $data = [
        'foo' => 'bar'
    ];
    $pdf = PDF::loadView('reports.welcome', $data);
    return $pdf->stream('document.pdf');
});




Route::group([
    'prefix'     => 'api',
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Api',
], function () { // custom admin routes

    Route::get('student', 'StudentController@index');
    Route::get('student/{id}', 'StudentController@show');

    Route::get('course', 'CourseController@index');
    Route::get('course/{id}', 'CourseController@show');

    Route::get('curriculum', 'CurriculumController@index');
    Route::get('curriculum/{id}', 'CurriculumController@show');
});
