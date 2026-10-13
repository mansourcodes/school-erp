<?php

namespace App\Http\Resources\V1;

use App\Models\StudentMarks;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An archive card (PLAN.md §3.3): course identity, dates and the published result.
 * Wraps a ClassRoom with course.academicPath loaded.
 */
class CourseSummaryResource extends JsonResource
{
    protected $studentMarks;

    protected $attendanceRate;

    public function __construct($classroom, ?StudentMarks $studentMarks, $attendanceRate)
    {
        parent::__construct($classroom);
        $this->studentMarks = $studentMarks;
        $this->attendanceRate = $attendanceRate;
    }

    public function toArray($request)
    {
        $course = $this->course;
        $finalGrade = $this->studentMarks ? $this->studentMarks->final_grade : null;
        $published = $finalGrade !== null && $finalGrade !== '';

        return [
            'id' => $course->id,
            'classroom_id' => $this->id,
            'status' => 'archived',
            'long_name' => $course->long_name,
            'academic_path' => optional($course->academicPath)->academic_path_name,
            'year' => $course->course_year,
            'hijri_year' => $course->hijri_year,
            'semester' => $course->semester,
            'start_date' => optional($course->start_date)->toDateString(),
            'end_date' => optional($course->end_date)->toDateString(),
            'class_room_number' => $this->class_room_number,
            'marks_published' => $published,
            'total_mark' => $published ? $this->studentMarks->total_mark : null,
            'final_grade' => $published ? $finalGrade : null,
            // PLAN.md §7.4; null when no session of the course was recorded.
            'attendance_rate' => $this->attendanceRate,
        ];
    }
}
