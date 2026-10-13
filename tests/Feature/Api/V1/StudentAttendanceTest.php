<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Services\Attendance\ClassroomAttendance;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

class StudentAttendanceTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        // Fridays 16:00–17:00: 11 Sep, 18 Sep, 25 Sep, 2 Oct … (the course starts Fri 4 Sep, which the report skips).
        $this->classroom = $this->enroll(['start_date' => '2026-09-04', 'end_date' => '2026-12-25'], [
            'attend_table' => [['curriculum_id' => (string) $this->curriculum->id, 'day' => '5', 'start_time' => '16:00', 'end_time' => '17:00']],
        ]);
        $this->record($this->classroom, '2026-09-11', '16:00', 'attend');
        $this->record($this->classroom, '2026-09-18', '16:05', 'late_w_excuse');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function record(ClassRoom $classroom, $date, $time, $state, $studentId = null)
    {
        $attend = Attends::create([
            'date' => $date,
            'start_time' => $time,
            'class_room_id' => $classroom->id,
            'course_id' => $classroom->course_id,
            'curriculum_id' => $this->curriculum->id,
        ]);
        $attend->{ClassroomAttendance::STATE_RELATIONS[$state]}()->attach($studentId ?: $this->student->id);
    }

    /** Local (Bahrain) wall-clock time; the frozen clock itself is UTC, like the server's. */
    protected function travelToLocal($datetime)
    {
        Carbon::setTestNow(Carbon::parse($datetime, 'Asia/Bahrain')->utc());
    }

    protected function fetch($uri)
    {
        return $this->actingAs($this->student, 'student')->getJson($uri);
    }

    public function test_requires_login()
    {
        $this->getJson('/api/v1/student/attendance')->assertStatus(401);
        $this->getJson('/api/v1/student/attendance/stats')->assertStatus(401);
    }

    public function test_lists_sessions_of_the_current_course_up_to_now()
    {
        $this->travelToLocal('2026-09-25 17:30');

        $this->fetch('/api/v1/student/attendance')
            ->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0', [
                'date' => '2026-09-11',
                'start' => '16:00',
                'end' => '17:00',
                'curriculum_id' => $this->curriculum->id,
                'curriculum' => 'تجويد القرآن الكريم',
                'teacher' => 'الشيخ أحمد',
                'state' => 'present',
                'other_classroom' => false,
            ])
            ->assertJsonPath('data.1.state', 'late_excused')
            // Today's session has started but was not recorded yet.
            ->assertJsonPath('data.2.date', '2026-09-25')
            ->assertJsonPath('data.2.state', 'unrecorded');
    }

    public function test_todays_session_is_not_listed_before_it_starts_unless_recorded()
    {
        $this->travelToLocal('2026-09-25 15:30');
        $this->fetch('/api/v1/student/attendance')->assertJsonCount(2, 'data');

        // Teachers may record a same-day session early (Q9).
        $this->record($this->classroom, '2026-09-25', '16:00', 'attend');
        $this->fetch('/api/v1/student/attendance')->assertJsonCount(3, 'data')->assertJsonPath('data.2.state', 'present');
    }

    public function test_today_follows_bahrain_time_not_utc()
    {
        // 18:30 in Bahrain is 15:30 UTC: the 16:00 session has started locally, though not in UTC.
        $this->travelToLocal('2026-09-25 18:30');

        $this->fetch('/api/v1/student/attendance')->assertJsonCount(3, 'data')->assertJsonPath('data.2.date', '2026-09-25');
    }

    public function test_filters_by_date_range()
    {
        $this->travelToLocal('2026-10-30 20:00');

        $this->fetch('/api/v1/student/attendance?from=2026-09-18&to=2026-10-02')
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.date', '2026-09-18')
            ->assertJsonPath('data.2.date', '2026-10-02');
    }

    public function test_stats_follow_the_metric_definitions()
    {
        $this->travelToLocal('2026-09-25 17:30');

        $this->fetch('/api/v1/student/attendance/stats')->assertExactJson(['data' => [
            'counts' => ['present' => 1, 'late' => 0, 'late_excused' => 1, 'absent' => 0, 'absent_excused' => 0, 'unrecorded' => 1],
            // Unrecorded sessions are excluded from every rate (Q2).
            'recorded' => 2,
            'attended' => 2,
            'rate' => 100,
            'punctual' => 50,
        ]]);
    }

    public function test_another_course_of_the_student_by_course_id()
    {
        $this->travelToLocal('2026-09-25 17:30');
        $old = $this->enroll(['start_date' => '2025-09-05', 'end_date' => '2025-09-26', 'is_active' => 0], [
            'attend_table' => [['curriculum_id' => (string) $this->curriculum->id, 'day' => '5', 'start_time' => '16:00']],
        ]);
        $this->record($old, '2025-09-12', '16:00', 'absent');

        $this->fetch("/api/v1/student/attendance?course_id={$old->course_id}")
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('data.0.state', 'absent')
            ->assertJsonPath('data.1.state', 'unrecorded');
        $this->fetch("/api/v1/student/attendance/stats?course_id={$old->course_id}")
            ->assertJsonPath('data.rate', 0)
            ->assertJsonPath('data.recorded', 1);
    }

    public function test_never_another_students_course_or_records()
    {
        $this->travelToLocal('2026-09-25 17:30');
        $other = Student::factory()->create();
        $foreign = $this->enroll([], [], $other);

        $this->fetch("/api/v1/student/attendance?course_id={$foreign->course_id}")->assertStatus(404);

        // A classmate's records never leak into this student's sessions.
        $this->classroom->students()->attach($other->id);
        $this->record($this->classroom, '2026-09-25', '16:00', 'absent', $other->id);
        $this->fetch('/api/v1/student/attendance')->assertJsonPath('data.2.state', 'unrecorded');
    }

    public function test_without_a_current_course()
    {
        $this->classroom->course->update(['is_active' => 0]);

        $this->fetch('/api/v1/student/attendance')->assertExactJson(['data' => []]);
        $this->fetch('/api/v1/student/attendance/stats')->assertExactJson(['data' => null]);
    }

    public function test_archive_cards_carry_the_attendance_rate()
    {
        $this->travelToLocal('2026-09-25 17:30');
        $this->classroom->course->update(['is_active' => 0]);
        $empty = $this->enroll(['start_date' => '2025-01-01', 'end_date' => '2025-01-02', 'is_active' => 0]);

        $this->fetch('/api/v1/student/courses?status=archived')
            ->assertJsonPath('data.0.id', $this->classroom->course_id)
            ->assertJsonPath('data.0.attendance_rate', 100)
            ->assertJsonPath('data.1.id', $empty->course_id)
            ->assertJsonPath('data.1.attendance_rate', null);
    }
}
