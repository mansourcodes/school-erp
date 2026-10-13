<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\CourseDetailResource;
use App\Models\Account\Payment;
use App\Models\ClassRoom;
use App\Models\StudentMarks;
use Illuminate\Http\Request;

/**
 * Student courses (PLAN.md §7.2). Every query is scoped to the signed-in
 * student; a course id from the URL is only matched against their enrollments.
 */
class StudentCourseController extends Controller
{
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
        $classroom = $student->classRooms()->where('class_rooms.course_id', $courseId)->first();
        abort_unless($classroom, 404);

        return $classroom;
    }
}
