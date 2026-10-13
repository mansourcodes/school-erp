<?php

namespace Tests\Feature\Api\V1\Concerns;

use App\Models\AcademicPath;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Student;
use App\Models\StudentMarks;

/**
 * Student + course + classroom fixtures in the real storage shapes
 * (teachers / attend_table JSON, nested marks JSON).
 */
trait BuildsEnrollments
{
    protected $student;

    protected $curriculum;

    protected function setUpEnrollments()
    {
        $this->student = Student::factory()->create();
        $this->curriculum = Curriculum::factory()->create([
            'curriculum_name' => 'تجويد القرآن الكريم',
            // Same nested shape the admin form stores: one object, each field a JSON string.
            'marks_labels' => json_encode([$this->markFields([
                ['label' => 'الاختبار الشفوي', 'mark' => '60'],
                ['label' => 'الحفظ', 'mark' => '40'],
            ])]),
        ]);
    }

    protected function markFields(array $finalExam)
    {
        $fields = array_fill_keys(array_keys(StudentMarks::$standard_marks_composer), '');
        $fields['finalexam_mark_details'] = json_encode($finalExam);

        return $fields;
    }

    protected function enroll(array $courseOverrides = [], array $classroomOverrides = [], $student = null)
    {
        $path = AcademicPath::factory()->create(['academic_path_name' => 'المسار الحوزوي']);
        $course = Course::factory()->create(array_merge([
            'academic_path_id' => $path->id,
            'is_active' => 1,
            'start_date' => '2026-09-06',
            'end_date' => '2026-12-24',
            'hijri_year' => '1448',
            'semester' => 'الفصل الأول',
        ], $courseOverrides));
        $classroom = ClassRoom::factory()->create(array_merge([
            'course_id' => $course->id,
            'class_room_number' => '2-ب',
            'class_room_name' => 'قاعة الإمام الصادق',
            'teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'الشيخ أحمد', 'user_id' => '5']],
            'attend_table' => [
                ['curriculum_id' => (string) $this->curriculum->id, 'day' => '7', 'start_time' => '16:00', 'end_time' => '17:15'],
                ['curriculum_id' => (string) $this->curriculum->id, 'day' => '2', 'start_time' => '17:30'],
                ['curriculum_id' => (string) $this->curriculum->id, 'day' => '5', 'start_time' => ''],
            ],
        ], $classroomOverrides));
        $classroom->students()->attach(($student ?: $this->student)->id);

        return $classroom;
    }

    protected function createMarks(ClassRoom $classroom, array $finalExam, $finalGrade, $total = null)
    {
        $fields = $this->markFields($finalExam);
        $fields['curriculum_id'] = (string) $this->curriculum->id;

        return StudentMarks::create([
            'student_id' => $this->student->id,
            'course_id' => $classroom->course_id,
            'marks' => json_encode([$fields]),
            'final_grade' => $finalGrade,
            'total_mark' => $total,
        ]);
    }
}
