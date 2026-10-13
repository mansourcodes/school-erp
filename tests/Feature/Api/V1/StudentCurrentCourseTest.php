<?php

namespace Tests\Feature\Api\V1;

use App\Models\AcademicPath;
use App\Models\Account\Payment;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Student;
use App\Models\StudentMarks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCurrentCourseTest extends TestCase
{
    use RefreshDatabase;

    protected $student;

    protected $curriculum;

    protected function setUp(): void
    {
        parent::setUp();

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

    protected function current()
    {
        return $this->actingAs($this->student, 'student')->getJson('/api/v1/student/courses/current');
    }

    public function test_requires_login()
    {
        $this->getJson('/api/v1/student/courses/current')->assertStatus(401);
    }

    public function test_no_active_enrollment_returns_null()
    {
        $this->enroll(['is_active' => 0]);

        $this->current()->assertOk()->assertExactJson(['data' => null]);
    }

    public function test_picks_the_latest_active_course_of_this_student_only()
    {
        $this->enroll(['start_date' => '2025-09-01']);
        $latest = $this->enroll(['start_date' => '2026-09-06']);
        $this->enroll(['start_date' => '2027-01-01', 'is_active' => 0]);
        $deleted = $this->enroll(['start_date' => '2027-02-01']);
        $deleted->course->delete();
        $this->enroll(['start_date' => '2027-03-01'], [], Student::factory()->create());

        $this->current()
            ->assertOk()
            ->assertJsonPath('data.classroom_id', $latest->id)
            ->assertJsonPath('data.status', 'active');
    }

    public function test_returns_the_course_detail_contract()
    {
        $classroom = $this->enroll();

        $response = $this->current();

        $response->assertOk()
            ->assertJsonPath('data.id', $classroom->course_id)
            ->assertJsonPath('data.long_name', $classroom->course->long_name)
            ->assertJsonPath('data.academic_path', 'المسار الحوزوي')
            ->assertJsonPath('data.start_date', '2026-09-06')
            ->assertJsonPath('data.class_room_number', '2-ب')
            ->assertJsonPath('data.curricula.0.name', 'تجويد القرآن الكريم')
            ->assertJsonPath('data.curricula.0.teacher', 'الشيخ أحمد')
            // ISO days → 0 = Sun: Sunday (7) first, then Tuesday (2), then Friday (5).
            ->assertJsonPath('data.curricula.0.slots', [
                ['day' => 0, 'start' => '16:00', 'end' => '17:15'],
                ['day' => 2, 'start' => '17:30', 'end' => '18:30'],
                ['day' => 5, 'start' => null, 'end' => null],
            ]);
        // Raw JSON columns never leave the server.
        $this->assertArrayNotHasKey('teachers', $response->json('data'));
        $this->assertArrayNotHasKey('attend_table', $response->json('data'));
    }

    public function test_lists_only_this_students_payments_for_this_course_with_arabic_labels()
    {
        $classroom = $this->enroll();
        $mine = Payment::create(['course_id' => $classroom->course_id, 'student_id' => $this->student->id, 'amount' => 30, 'type' => 'FULL', 'source' => 'cash']);
        Payment::create(['course_id' => $classroom->course_id, 'student_id' => Student::factory()->create()->id, 'amount' => 99, 'type' => 'FULL', 'source' => 'cash']);
        $otherCourse = $this->enroll(['is_active' => 0]);
        Payment::create(['course_id' => $otherCourse->course_id, 'student_id' => $this->student->id, 'amount' => 77, 'type' => 'PART', 'source' => 'BENEFIT_PAY']);

        $this->current()
            ->assertJsonCount(1, 'data.payments')
            ->assertJsonPath('data.payments.0', [
                'id' => $mine->id,
                'date' => $mine->created_at->toDateString(),
                'amount' => 30,
                'type' => 'رسوم كاملة',
                'source' => 'نقدًا',
            ]);
    }

    public function test_marks_in_progress_hide_the_total_and_grade()
    {
        $classroom = $this->enroll();
        $this->createMarks($classroom, [['label' => 'الاختبار الشفوي', 'mark' => 51]], null);

        $this->current()
            ->assertJsonPath('data.marks_published', false)
            ->assertJsonPath('data.marks', [
                ['key' => 'm0', 'label' => 'الاختبار الشفوي', 'max' => 60, 'mark' => 51],
                ['key' => 'm1', 'label' => 'الحفظ', 'max' => 40, 'mark' => null],
            ])
            ->assertJsonPath('data.total_mark', null)
            ->assertJsonPath('data.final_grade', null);
    }

    public function test_published_marks_show_the_total_and_grade()
    {
        $classroom = $this->enroll();
        $this->createMarks($classroom, [['label' => 'الاختبار الشفوي', 'mark' => 51], ['label' => 'الحفظ', 'mark' => 38]], 'ممتاز', 89);

        $this->current()
            ->assertJsonPath('data.marks_published', true)
            ->assertJsonPath('data.total_mark', 89)
            ->assertJsonPath('data.final_grade', 'ممتاز');
    }

    public function test_certificate_is_forbidden_until_the_final_grade_is_set()
    {
        $classroom = $this->enroll();
        $this->createMarks($classroom, [['label' => 'الحفظ', 'mark' => 38]], null);

        $this->actingAs($this->student, 'student')
            ->getJson("/api/v1/student/courses/{$classroom->course_id}/certificate")
            ->assertStatus(403);
    }

    public function test_certificate_renders_the_print_view_when_published()
    {
        $classroom = $this->enroll();
        $this->createMarks($classroom, [['label' => 'الحفظ', 'mark' => 38]], 'ممتاز', 89);

        $this->actingAs($this->student, 'student')
            ->get("/api/v1/student/courses/{$classroom->course_id}/certificate")
            ->assertOk()
            ->assertSee($this->student->name)
            ->assertSee('ممتاز');
    }

    public function test_certificate_of_a_course_the_student_is_not_in_is_not_found()
    {
        $foreign = $this->enroll([], [], Student::factory()->create());

        $this->actingAs($this->student, 'student')
            ->getJson("/api/v1/student/courses/{$foreign->course_id}/certificate")
            ->assertStatus(404);
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
