<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/course', [App\Http\Controllers\Api\CourseController::class, 'index']);
Route::get('/classroom', [App\Http\Controllers\Api\ClassRoomsController::class, 'index']);
Route::get('/student', [App\Http\Controllers\Api\StudentController::class, 'index']);
Route::get('/teacher', [App\Http\Controllers\Api\TeacherController::class, 'index']);
Route::get('/teacher/{id}', [App\Http\Controllers\Api\TeacherController::class, 'show']);

/*
| Portal API v1 (/beta SPA, PLAN.md §7). Cookie sessions via Sanctum, same origin.
*/
Route::prefix('v1')
    ->middleware([
        \App\Http\Middleware\ForceJsonResponse::class,
        'portal.mode:api',
        \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
    ])
    ->group(function () {
        Route::post('/auth/login', [App\Http\Controllers\Api\V1\AuthController::class, 'login'])
            ->middleware('throttle:student-login');

        Route::middleware(['auth:sanctum', 'throttle:portal'])->group(function () {
            Route::post('/auth/logout', [App\Http\Controllers\Api\V1\AuthController::class, 'logout']);
            Route::get('/me', [App\Http\Controllers\Api\V1\MeController::class, 'show']);

            // Teacher panel (PLAN.md §8): own classrooms of the current course only.
            Route::prefix('teacher')->middleware('portal.role:teacher')->group(function () {
                Route::get('/today', [App\Http\Controllers\Api\V1\TeacherController::class, 'today']);
                Route::get('/classrooms', [App\Http\Controllers\Api\V1\TeacherController::class, 'classrooms']);
                Route::get('/classrooms/{id}', [App\Http\Controllers\Api\V1\TeacherController::class, 'classroom'])->where('id', '[0-9]+');
                Route::get('/classrooms/{id}/sessions', [App\Http\Controllers\Api\V1\TeacherController::class, 'classroomSessions'])->where('id', '[0-9]+');
                Route::get('/sessions/{code}', [App\Http\Controllers\Api\V1\TeacherController::class, 'session']);
                Route::put('/sessions/{code}', [App\Http\Controllers\Api\V1\TeacherController::class, 'saveSession']);
                Route::get('/stats', [App\Http\Controllers\Api\V1\TeacherController::class, 'stats']);
                Route::post('/classrooms/{id}/students/{sid}/photo', [App\Http\Controllers\Api\V1\TeacherController::class, 'uploadPhoto'])
                    ->where(['id' => '[0-9]+', 'sid' => '[0-9]+'])->middleware('throttle:student-photo');
                Route::delete('/classrooms/{id}/students/{sid}/photo', [App\Http\Controllers\Api\V1\TeacherController::class, 'deletePhoto'])
                    ->where(['id' => '[0-9]+', 'sid' => '[0-9]+']);
            });

            Route::prefix('student')->middleware('portal.role:student')->group(function () {
                Route::get('/courses', [App\Http\Controllers\Api\V1\StudentCourseController::class, 'index']);
                Route::get('/courses/current', [App\Http\Controllers\Api\V1\StudentCourseController::class, 'current']);
                Route::get('/courses/{course}', [App\Http\Controllers\Api\V1\StudentCourseController::class, 'show'])
                    ->where('course', '[0-9]+');
                Route::get('/profile', [App\Http\Controllers\Api\V1\MeController::class, 'profile']);
                Route::get('/attendance', [App\Http\Controllers\Api\V1\StudentAttendanceController::class, 'index']);
                Route::get('/attendance/stats', [App\Http\Controllers\Api\V1\StudentAttendanceController::class, 'stats']);
                Route::get('/courses/{course}/certificate', [App\Http\Controllers\Api\V1\StudentCourseController::class, 'certificate'])
                    ->where('course', '[0-9]+');
            });
        });
    });
