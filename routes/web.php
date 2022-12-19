<?php

use App\Http\Controllers\Report\AccountController;
use App\Http\Controllers\Report\CourseController;
use App\Http\Controllers\Report\StatisticController;
use App\Http\Controllers\Report\StudentController;
use App\Jobs\generateExamsDocxJob;
use App\Jobs\zipExamFilesJob;
use App\Models\ExamTool;
use App\Models\Student;
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

    $student = Student::find(52);

    $list = [];
    foreach ($student->classRooms as $key => $classRoom) {
        $list[] = [
            'label' => $classRoom->course->long_name,
            'url' => [
                [
                    'label' => $classRoom->course->long_name,
                    'url' => backpack_url('studentReports/SingleStudentTable' . '?course=' . $classRoom->course->id . '&student=' . $student->id),
                ],
                [
                    'label' => $classRoom->course->long_name,
                    'url' => backpack_url('studentReports/SingleUpdateStudentsInfo' . '?course=' . $classRoom->course->id . '&student=' . $student->id),
                ],
            ]

        ];
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
    Route::get('courseReports', [CourseController::class, 'print']);
    Route::get('accountReports', [AccountController::class, 'print']);
    Route::get('statisticReports', [StatisticController::class, 'print']);


    // student reports
    Route::get('studentReports', [StudentController::class, 'print']);
    Route::get('studentReports/SingleStudentTable', [StudentController::class, 'SingleStudentTable']);
    Route::get('studentReports/SingleUpdateStudentsInfo', [StudentController::class, 'SingleUpdateStudentsInfo']);



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
