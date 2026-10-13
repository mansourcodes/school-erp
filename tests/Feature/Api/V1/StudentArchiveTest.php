<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

class StudentArchiveTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
    }

    protected function archived()
    {
        return $this->actingAs($this->student, 'student')->getJson('/api/v1/student/courses?status=archived');
    }

    public function test_lists_every_enrollment_except_the_current_one_newest_first()
    {
        $current = $this->enroll(['start_date' => '2026-09-06']);
        $older = $this->enroll(['start_date' => '2025-02-01', 'is_active' => 0]);
        $newer = $this->enroll(['start_date' => '2026-02-01', 'is_active' => 0]);
        $deleted = $this->enroll(['start_date' => '2025-09-01', 'is_active' => 0]);
        $deleted->course->delete();
        $this->enroll(['start_date' => '2026-03-01', 'is_active' => 0], [], Student::factory()->create());

        $response = $this->archived()->assertOk();

        $this->assertSame([$newer->course_id, $older->course_id], array_column($response->json('data'), 'id'));
        $this->assertNotContains($current->course_id, array_column($response->json('data'), 'id'));
        $response->assertJsonPath('data.0.status', 'archived');
    }

    public function test_an_older_active_course_is_archived_too()
    {
        $this->enroll(['start_date' => '2026-09-06']);
        $olderActive = $this->enroll(['start_date' => '2025-09-06']);

        $this->archived()->assertJsonPath('data.0.id', $olderActive->course_id)->assertJsonCount(1, 'data');
    }

    public function test_one_card_per_course_when_enrolled_in_two_of_its_classrooms()
    {
        $classroom = $this->enroll(['is_active' => 0]);
        $second = $this->enroll(['is_active' => 0]);
        $second->update(['course_id' => $classroom->course_id]);

        $this->archived()->assertJsonCount(1, 'data')->assertJsonPath('data.0.classroom_id', $second->id);
    }

    public function test_summary_shows_the_result_only_once_published()
    {
        $published = $this->enroll(['start_date' => '2026-02-01', 'is_active' => 0]);
        $pending = $this->enroll(['start_date' => '2025-02-01', 'is_active' => 0]);
        $this->createMarks($published, [['label' => 'الحفظ', 'mark' => 38]], 'ممتاز', 91.5);
        $this->createMarks($pending, [['label' => 'الحفظ', 'mark' => 20]], null, 50);

        $this->archived()
            ->assertJsonPath('data.0.marks_published', true)
            ->assertJsonPath('data.0.total_mark', 91.5)
            ->assertJsonPath('data.0.final_grade', 'ممتاز')
            ->assertJsonPath('data.1.marks_published', false)
            ->assertJsonPath('data.1.total_mark', null);
    }

    public function test_status_filter_is_required()
    {
        $this->actingAs($this->student, 'student')
            ->getJson('/api/v1/student/courses')
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_detail_of_an_archived_course_is_read_only()
    {
        $this->enroll(['start_date' => '2026-09-06']);
        $old = $this->enroll(['start_date' => '2025-02-01', 'is_active' => 0]);

        $this->actingAs($this->student, 'student')
            ->getJson("/api/v1/student/courses/{$old->course_id}")
            ->assertOk()
            ->assertJsonPath('data.status', 'archived')
            ->assertJsonPath('data.classroom_id', $old->id)
            ->assertJsonPath('data.curricula.0.name', 'تجويد القرآن الكريم');
    }

    public function test_detail_of_the_current_course_is_active()
    {
        $current = $this->enroll();

        $this->actingAs($this->student, 'student')
            ->getJson("/api/v1/student/courses/{$current->course_id}")
            ->assertJsonPath('data.status', 'active');
    }

    public function test_detail_of_someone_elses_or_a_deleted_course_is_not_found()
    {
        $foreign = $this->enroll(['is_active' => 0], [], Student::factory()->create());
        $deleted = $this->enroll(['is_active' => 0]);
        $deleted->course->delete();

        foreach ([$foreign->course_id, $deleted->course_id, 999999] as $courseId) {
            $this->actingAs($this->student, 'student')
                ->getJson("/api/v1/student/courses/{$courseId}")
                ->assertStatus(404);
        }
    }
}
