<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes
    Route::crud('academicpath', 'AcademicPathCrudController');
    Route::crud('classroom', 'ClassRoomCrudController');
    Route::crud('course', 'CourseCrudController');
    Route::crud('curriculum', 'CurriculumCrudController');
    Route::crud('curriculumcategory', 'CurriculumCategoryCrudController');
    Route::crud('student', 'StudentCrudController');
    Route::crud('studentmarks', 'StudentMarksCrudController');
    Route::crud('attends', 'AttendsCrudController');
}); // this should be the absolute last line of this file
