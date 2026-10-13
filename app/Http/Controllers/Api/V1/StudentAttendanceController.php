<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Attendance\StudentSessions;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * The signed-in student's attendance (PLAN.md §7.2). `course_id` defaults to the
 * current course and is only matched against the student's own enrollments.
 */
class StudentAttendanceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'course_id' => ['nullable', 'integer'],
            'from' => ['nullable', 'date_format:Y-m-d'],
            'to' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $sessions = collect($this->sessions($request))
            ->filter(function ($session) use ($request) {
                return (! $request->input('from') || $session['date'] >= $request->input('from'))
                    && (! $request->input('to') || $session['date'] <= $request->input('to'));
            })
            ->values();

        return response()->json(['data' => $sessions]);
    }

    public function stats(Request $request)
    {
        $request->validate(['course_id' => ['nullable', 'integer']]);

        $sessions = $this->sessions($request);

        return response()->json(['data' => $sessions === null ? null : StudentSessions::stats($sessions)]);
    }

    /** null when there is no course to show (no course_id and no current course). */
    protected function sessions(Request $request)
    {
        $student = $request->user();

        if ($request->filled('course_id')) {
            $classroom = $student->enrolledClassroomFor($request->input('course_id'));
            abort_unless($classroom, 404);
        } else {
            $classroom = $student->currentClassroom();
            if (! $classroom) {
                return null;
            }
        }

        return StudentSessions::forStudent($classroom, $student->id, Carbon::now(config('portal.timezone')));
    }
}
