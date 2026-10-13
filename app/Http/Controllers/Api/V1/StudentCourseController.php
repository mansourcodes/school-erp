<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseDetailResource;
use App\Http\Resources\V1\CourseSummaryResource;
use App\Models\Account\Payment;
use App\Models\ClassRoom;
use App\Models\StudentMarks;
use App\Services\Attendance\StudentSessions;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Student courses (PLAN.md §7.2). Every query is scoped to the signed-in
 * student; a course id from the URL is only matched against their enrollments.
 */
class StudentCourseController extends Controller
{
    /** GET /student/courses?status=archived — every enrollment except the current one, newest first. */
    public function index(Request $request)
    {
        $request->validate(['status' => ['required', 'in:archived']]);

        $student = $request->user();
        $classrooms = $student->archivedClassrooms();
        $classrooms->load('course.academicPath');

        $marks = StudentMarks::where('student_id', $student->id)
            ->whereIn('course_id', $classrooms->pluck('course_id'))
            ->get()
            ->keyBy('course_id');

        $now = Carbon::now(config('portal.timezone'));

        return response()->json([
            'data' => $classrooms->map(function ($classroom) use ($marks, $student, $now) {
                $stats = StudentSessions::stats(StudentSessions::forStudent($classroom, $student->id, $now));

                return new CourseSummaryResource($classroom, $marks->get($classroom->course_id), $stats['rate']);
            })->values(),
        ]);
    }

    /** GET /student/courses/{course} — one of the student's courses; archived ones are read-only. */
    public function show(Request $request, $courseId)
    {
        $student = $request->user();
        $classroom = $this->enrolledClassroom($student, $courseId);
        $current = $student->currentClassroom();
        $status = $current && (int) $current->course_id === (int) $classroom->course_id ? 'active' : 'archived';

        return $this->detail($student, $classroom, $status);
    }

    public function current(Request $request)
    {
        $student = $request->user();
        $classroom = $student->currentClassroom();

        if (! $classroom) {
            return response()->json(['data' => null]);
        }

        return $this->detail($student, $classroom, 'active');
    }

    /**
     * The certificate print view the admin uses (`backend.reports.certificate`),
     * opened in a new tab; the student prints it or saves it as PDF.
     */
    public function certificate(Request $request, $courseId)
    {
        $student = $request->user();
        $this->enrolledClassroom($student, $courseId);

        $studentMarks = StudentMarks::where('student_id', $student->id)->where('course_id', $courseId)->first();
        if (! $studentMarks || $studentMarks->final_grade === null || $studentMarks->final_grade === '') {
            abort(403);
        }

        return response()->view('backend.reports.certificate', [
            'studentmarks' => [$studentMarks],
            'print' => 'print',
        ]);
    }

    protected function detail($student, ClassRoom $classroom, $status)
    {
        $classroom->loadMissing('course.academicPath');

        $payments = Payment::where('student_id', $student->id)
            ->where('course_id', $classroom->course_id)
            ->orderBy('created_at')
            ->get();

        $studentMarks = StudentMarks::where('student_id', $student->id)
            ->where('course_id', $classroom->course_id)
            ->first();

        return new CourseDetailResource($classroom, $payments, $studentMarks, $status);
    }

    protected function enrolledClassroom($student, $courseId)
    {
        $classroom = $student->enrolledClassroomFor($courseId);
        abort_unless($classroom, 404);

        return $classroom;
    }
}
