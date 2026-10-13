<?php

namespace Tests\Feature\Attendance;

use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Student;
use App\Services\Attendance\ClassroomAttendance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/**
 * The cell rule shared by the admin attend list and the portal (PLAN.md §5.5).
 */
class ClassroomAttendanceTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $classroom;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        // Friday 4 Sep 2026 → Friday 2 Oct 2026, Friday 16:00 slot.
        $this->classroom = $this->enroll(['start_date' => '2026-09-04', 'end_date' => '2026-10-02'], [
            'attend_table' => [['curriculum_id' => (string) $this->curriculum->id, 'day' => '5', 'start_time' => '16:00']],
        ]);
    }

    /** One Attends row with its per-state pivots: [student_id => state]. */
    protected function record(ClassRoom $classroom, $date, $time, $curriculumId, array $states)
    {
        $attend = Attends::create([
            'date' => $date,
            'start_time' => $time,
            'class_room_id' => $classroom->id,
            'course_id' => $classroom->course_id,
            'curriculum_id' => $curriculumId,
        ]);
        foreach ($states as $studentId => $state) {
            $attend->{ClassroomAttendance::STATE_RELATIONS[$state]}()->attach($studentId);
        }

        return $attend;
    }

    protected function attendance(array $studentIds = null)
    {
        return new ClassroomAttendance($this->classroom->fresh(), $studentIds);
    }

    public function test_matches_the_slot_by_hour_even_with_wrong_minutes()
    {
        // Legacy 'H:m' bug: minutes held the month.
        $this->record($this->classroom, '2026-09-11', '16:09', $this->curriculum->id, [$this->student->id => 'late']);

        $this->assertSame(['late', false], $this->attendance()->stateFor($this->curriculum->id, '2026-09-11', '16', $this->student->id));
    }

    public function test_two_slots_of_one_subject_on_one_day_keep_their_own_records()
    {
        $this->record($this->classroom, '2026-09-11', '16:00', $this->curriculum->id, [$this->student->id => 'attend']);
        $this->record($this->classroom, '2026-09-11', '18:00', $this->curriculum->id, [$this->student->id => 'absent']);
        $attendance = $this->attendance();

        $this->assertSame('attend', $attendance->stateFor($this->curriculum->id, '2026-09-11', '16', $this->student->id)[0]);
        $this->assertSame('absent', $attendance->stateFor($this->curriculum->id, '2026-09-11', '18', $this->student->id)[0]);
    }

    public function test_a_slot_without_a_time_uses_any_record_of_that_subject_and_day()
    {
        // The form records the time it was opened when the timetable has none.
        $this->record($this->classroom, '2026-09-11', '18:42', $this->curriculum->id, [$this->student->id => 'absent_w_excuse']);

        $this->assertSame(['absent_w_excuse', false], $this->attendance()->stateFor($this->curriculum->id, '2026-09-11', null, $this->student->id));
    }

    public function test_a_moved_student_gets_the_record_of_their_previous_classroom()
    {
        $previous = $this->enroll([], [], Student::factory()->create());
        $previous->update(['course_id' => $this->classroom->course_id]);
        $this->record($previous->fresh(), '2026-09-11', '16:00', $this->curriculum->id, [$this->student->id => 'attend']);

        $this->assertSame(['attend', true], $this->attendance()->stateFor($this->curriculum->id, '2026-09-11', '16', $this->student->id));
    }

    public function test_a_moved_student_falls_back_to_any_subject_of_the_course_that_day()
    {
        $previous = $this->enroll([], [], Student::factory()->create());
        $previous->update(['course_id' => $this->classroom->course_id]);
        $this->record($previous->fresh(), '2026-09-11', '10:00', 9999, [$this->student->id => 'late_w_excuse']);

        $this->assertSame(['late_w_excuse', true], $this->attendance()->stateFor($this->curriculum->id, '2026-09-11', '16', $this->student->id));
    }

    public function test_a_record_in_this_classroom_wins_over_the_previous_one()
    {
        $previous = $this->enroll([], [], Student::factory()->create());
        $previous->update(['course_id' => $this->classroom->course_id]);
        $this->record($previous->fresh(), '2026-09-11', '16:00', $this->curriculum->id, [$this->student->id => 'absent']);
        $this->record($this->classroom, '2026-09-11', '16:00', $this->curriculum->id, [$this->student->id => 'attend']);

        $this->assertSame(['attend', false], $this->attendance()->stateFor($this->curriculum->id, '2026-09-11', '16', $this->student->id));
    }

    public function test_records_of_students_outside_the_list_are_ignored()
    {
        $removed = Student::factory()->create();
        $this->record($this->classroom, '2026-09-11', '16:00', $this->curriculum->id, [$removed->id => 'attend']);

        $this->assertSame([null, false], $this->attendance()->stateFor($this->curriculum->id, '2026-09-11', '16', $removed->id));
        $this->assertSame('attend', $this->attendance([$removed->id])->stateFor($this->curriculum->id, '2026-09-11', '16', $removed->id)[0]);
    }

    public function test_slot_dates_follow_the_admin_report_including_the_start_date_quirk()
    {
        $slot = $this->attendance()->slots()[0];

        // getDaysInRange uses modify('next Friday'), so a course starting on a Friday skips that first Friday.
        $this->assertSame(['2026-09-11', '2026-09-18', '2026-09-25', '2026-10-02'], $slot['dates']);
        $this->assertSame('16', $slot['start']->format('H'));
    }
}
