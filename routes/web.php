<?php

use App\Http\Controllers\Report\AccountController;
use App\Http\Controllers\Report\CourseController;
use App\Http\Controllers\Report\StatisticController;
use App\Http\Controllers\Report\StudentController;
use App\Jobs\generateExamsDocxJob;
use App\Jobs\zipExamFilesJob;
use App\Models\ExamTool;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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
Route::get('/debug', function () {

    $list = [];
    $class_methods = get_class_methods(new StudentController());
    foreach ($class_methods as $method_name) {
        $functionOriginalName = substr($method_name, 0, -1);
        if (substr($method_name, -1) === '_') {

            array_push($list, [
                'label' => trans('reports.' . Str::of($functionOriginalName)->snake()),
                'url' => backpack_url('reports?view=' . $functionOriginalName . '&course='),
            ]);
        }
    }

    dd($list);
});


/*
|--------------------------------------------------------------------------
| admin Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    Route::get('reports', 'AcademiaReportsController@print');
    Route::get('studentReports', [StudentController::class, 'print']);
    Route::get('courseReports', [CourseController::class, 'print']);
    Route::get('accountReports', [AccountController::class, 'print']);
    Route::get('statisticReports', [StatisticController::class, 'print']);


    Route::get('add_attend_by_date', 'AttendsCrudController@addAttendByDate');
    Route::get('attend_easy_form', 'AttendsCrudController@AttendEasyForm');
    Route::get('save_attend_easy_form', 'AttendsCrudController@SaveAttendEasyForm');
    Route::get('quick_delete_and_add', 'AttendsCrudController@QuickDeleteAndAdd');
});


/*
|--------------------------------------------------------------------------
| Api Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix'     => 'api',
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Api',
], function () { // api routes

    Route::get('student', 'StudentController@index');
    Route::get('student/{id}', 'StudentController@show');

    Route::get('course', 'CourseController@index');
    Route::get('course/{id}', 'CourseController@show');

    Route::get('curriculum', 'CurriculumController@index');
    Route::get('curriculum/{id}', 'CurriculumController@show');
    Route::get('course/{id}', 'CourseController@show');

    Route::get('classroom', 'ClassRoomsController@index');
    Route::get('classroom/{id}', 'ClassRoomsController@show');
});
