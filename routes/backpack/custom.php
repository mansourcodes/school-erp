<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

use App\Http\Controllers\Pull\OldToNewDbController;
use Illuminate\Support\Facades\Route;

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () {



    Route::get('reports', 'AcademiaReportsController@print');
    Route::get('courseReports', [App\Http\Controllers\Report\CourseController::class, 'print']);
    Route::get('accountReports', [App\Http\Controllers\Report\AccountController::class, 'print']);
    Route::get('statisticReports', [App\Http\Controllers\Report\StatisticController::class, 'print']);
    Route::get('paymentReports', [App\Http\Controllers\Report\PaymentController::class, 'print']);


    // student reports
    Route::get('studentReports', [App\Http\Controllers\Report\StudentController::class, 'print']);
    Route::get('studentReports/SingleStudentTable', [App\Http\Controllers\Report\StudentController::class, 'singleStudentTable']);
    Route::get('studentReports/SingleUpdateStudentsInfo', [App\Http\Controllers\Report\StudentController::class, 'singleUpdateStudentsInfo']);



    Route::get('add_attend_by_date', 'AttendsCrudController@addAttendByDate');
    Route::get('attend_easy_form', 'AttendsCrudController@AttendEasyForm');
    Route::get('save_attend_easy_form', 'AttendsCrudController@SaveAttendEasyForm');
    Route::get('quick_delete_and_add', 'AttendsCrudController@QuickDeleteAndAdd');

    // 

    Route::get('addMarksByClass/{id}', 'ClassRoomMarksController@addMarksByClass');
    Route::post('saveMarksByClassForm', 'ClassRoomMarksController@saveMarksByClassJson');


    // custom admin routes
    Route::crud('academicpath', 'AcademicPathCrudController');
    Route::crud('classroom', 'ClassRoomCrudController');
    Route::crud('course', 'CourseCrudController');
    Route::crud('curriculum', 'CurriculumCrudController');
    Route::crud('curriculumcategory', 'CurriculumCategoryCrudController');
    Route::crud('student', 'StudentCrudController');
    Route::crud('studentmarks', 'StudentMarksCrudController');
    Route::crud('attends', 'AttendsCrudController');
    Route::crud('payment', 'PaymentCrudController');
    Route::crud('examtool', 'ExamToolCrudController');
}); // this should be the absolute last line of this file



/*
|--------------------------------------------------------------------------
| Pull Old Database Routes
|--------------------------------------------------------------------------
*/

Route::group([
    'prefix'     => 'pull'
], function () { // routes

    Route::get('pullClassRoom', [OldToNewDbController::class, 'pullClassRoom']);
    Route::get('emptyTables', [OldToNewDbController::class, 'emptyTables']);
});
