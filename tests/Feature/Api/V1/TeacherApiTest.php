<?php

namespace Tests\Feature\Api\V1;

use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Curriculum;
use App\Models\Student;
use App\Models\User;
use App\Services\Attendance\ClassroomAttendance;
use App\Services\Attendance\StudentSessions;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/** PLAN.md §8: teacher login, scope (own current classrooms + subjects), roster read/save, stats. */
class TeacherApiTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $teacher;

    protected $classroom;

    protected $classmate;

    protected $otherSubject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        Carbon::setTestNow(Carbon::parse('2026-09-25 17:30', 'Asia/Bahrain')->utc());

        Role::findOrCreate('Teacher', 'web');
        $this->teacher = User::factory()->create(['username' => 'teacher.ali', 'email' => 'teacher@example.com', 'password' => Hash::make('secret-pass')]);
        $this->teacher->assignRole('Teacher');

        $this->otherSubject = Curriculum::factory()->create(['curriculum_name' => 'الفقه']);
        // Fridays: tajweed 16:00 (this teacher) and fiqh 18:00 (another teacher).
        $this->classroom = $this->enroll(['start_date' => '2026-09-04', 'end_date' => '2026-12-25'], [
            'class_room_number' => '2-ب',
            'teachers' => [
                ['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'الشيخ أحمد', 'user_id' => (string) $this->teacher->id],
                ['curriculum_id' => (string) $this->otherSubject->id, 'teacher_name' => 'الشيخ محمد', 'user_id' => '999'],
            ],
            'attend_table' => [
                ['curriculum_id' => (string) $this->curriculum->id, 'day' => '5', 'start_time' => '16:00', 'end_time' => '17:00'],
                ['curriculum_id' => (string) $this->otherSubject->id, 'day' => '5', 'start_time' => '18:00'],
            ],
        ]);
        $this->classmate = Student::factory()->create(['name' => 'علي']);
        $this->classroom->students()->attach($this->classmate->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function asTeacher()
    {
        return $this->actingAs($this->teacher, 'web');
    }

    protected function code($date, $time = '16:00', $classroom = null, $curriculum = null)
    {
        return urlencode($date . '#' . $time . '#' . ($classroom ?: $this->classroom)->id . '#' . ($curriculum ?: $this->curriculum)->id);
    }

    public function test_teachers_log_in_with_their_username_and_get_the_teacher_role()
    {
        $this->withHeaders(['Referer' => 'http://localhost/beta/login'])
            ->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => ' Teacher.Ali ', 'password' => 'secret-pass'])
            ->assertOk()
            ->assertJsonPath('data.role', 'teacher')
            ->assertJsonPath('data.username', 'teacher.ali')
            ->assertJsonPath('data.email', 'teacher@example.com');
    }

    public function test_the_email_no_longer_signs_a_teacher_in()
    {
        $this->withHeaders(['Referer' => 'http://localhost/beta/login'])
            ->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => 'teacher@example.com', 'password' => 'secret-pass'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('username');
    }

    public function test_admin_users_without_the_teacher_role_cannot_use_the_portal()
    {
        User::factory()->create(['username' => 'admin', 'password' => Hash::make('secret-pass')]);

        $this->withHeaders(['Referer' => 'http://localhost/beta/login'])
            ->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => 'admin', 'password' => 'secret-pass'])
            ->assertStatus(422)
            ->assertJsonPath('message', __('portal.auth.failed_teacher'))
            ->assertJsonValidationErrors('username');
    }

    // One identity per test: Sanctum's guard keeps the user it resolved for the app's lifetime,
    // and every request in a PHPUnit test shares one app (real requests do not).
    public function test_a_teacher_cannot_use_student_endpoints()
    {
        $this->asTeacher()->getJson('/api/v1/student/courses/current')->assertStatus(403);
    }

    public function test_a_student_cannot_use_teacher_endpoints()
    {
        $this->actingAs($this->student, 'student')->getJson('/api/v1/teacher/today')->assertStatus(403);
    }

    public function test_classrooms_are_only_mine_in_the_current_course_with_my_subjects()
    {
        // Another teacher's classroom and my classroom in an older active course are hidden.
        $this->enroll(['start_date' => '2026-09-04'], ['teachers' => [['curriculum_id' => '1', 'teacher_name' => 'x', 'user_id' => '999']]]);
        $this->enroll(['start_date' => '2025-09-05'], [
            'teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'x', 'user_id' => (string) $this->teacher->id]],
        ]);

        $this->asTeacher()->getJson('/api/v1/teacher/classrooms')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->classroom->id)
            ->assertJsonPath('data.0.subjects', [['id' => $this->curriculum->id, 'name' => 'تجويد القرآن الكريم']])
            ->assertJsonPath('data.0.slots', [['curriculum_id' => $this->curriculum->id, 'day' => 5, 'start' => '16:00', 'end' => '17:00']])
            ->assertJsonPath('data.0.students_count', 2)
            // 11 and 18 Sep never recorded; 25 Sep (today, started) is open, not pending.
            ->assertJsonPath('data.0.pending_count', 2);
    }

    public function test_a_classroom_carries_its_course_details()
    {
        $this->asTeacher()->getJson("/api/v1/teacher/classrooms/{$this->classroom->id}")
            ->assertOk()
            ->assertJsonPath('data.course.id', $this->classroom->course->id)
            ->assertJsonPath('data.course.long_name', $this->classroom->course->long_name)
            ->assertJsonPath('data.course.start_date', '2026-09-04')
            ->assertJsonPath('data.course.end_date', '2026-12-25');
    }

    public function test_today_lists_sessions_pending_and_upcoming()
    {
        $this->asTeacher()->getJson('/api/v1/teacher/today')
            ->assertOk()
            ->assertJsonPath('data.date', '2026-09-25')
            ->assertJsonCount(1, 'data.today')
            ->assertJsonPath('data.today.0.status', 'open')
            ->assertJsonPath('data.today.0.classroom.number', '2-ب')
            ->assertJsonCount(2, 'data.pending')
            ->assertJsonPath('data.upcoming.0.date', '2026-10-02')
            ->assertJsonPath('data.upcoming.0.editable', false)
            ->assertJsonPath('data.tiles', ['classrooms' => 1, 'students' => 2, 'rate' => null, 'pending' => 2]);
    }

    public function test_saving_a_roster_is_idempotent_and_shared_with_the_reports()
    {
        $states = [$this->student->id => 'present', $this->classmate->id => 'absent_excused'];

        $this->asTeacher()->putJson('/api/v1/teacher/sessions/' . $this->code('2026-09-18'), ['states' => $states])
            ->assertOk()
            ->assertJsonPath('data.created', true);
        // A retry (e.g. the offline queue) changes nothing.
        $this->asTeacher()->putJson('/api/v1/teacher/sessions/' . $this->code('2026-09-18'), ['states' => $states])
            ->assertJsonPath('data.created', false);
        $this->assertSame(1, Attends::count());

        // Edit: a later save replaces the states.
        $states[$this->classmate->id] = 'late';
        $this->asTeacher()->putJson('/api/v1/teacher/sessions/' . $this->code('2026-09-18'), ['states' => $states])->assertOk();

        $roster = $this->asTeacher()->getJson('/api/v1/teacher/sessions/' . $this->code('2026-09-18'))
            ->assertOk()
            ->assertJsonPath('data.session.status', 'recorded')
            ->assertJsonPath('data.session.counts.present', 1)
            ->assertJsonPath('data.session.counts.late', 1)
            ->assertJsonPath('data.previous_code', urldecode($this->code('2026-09-11')))
            ->assertJsonPath('data.next_code', urldecode($this->code('2026-09-25')));
        $states = collect($roster->json('data.students'))->pluck('state', 'id');
        $this->assertSame(['present', 'late'], [$states[$this->student->id], $states[$this->classmate->id]]);

        // The admin report and the student portal read the same record.
        $attendance = new ClassroomAttendance($this->classroom->fresh());
        $this->assertSame(['attend', false], $attendance->stateFor($this->curriculum->id, '2026-09-18', '16', $this->student->id));
        $studentView = StudentSessions::forStudent($this->classroom->fresh(), $this->student->id, Carbon::now('Asia/Bahrain'));
        $this->assertSame('present', collect($studentView)->firstWhere('date', '2026-09-18')['state']);
    }

    public function test_a_same_day_session_can_be_recorded_early_but_not_a_later_day()
    {
        Carbon::setTestNow(Carbon::parse('2026-09-25 09:00', 'Asia/Bahrain')->utc());
        $states = [$this->student->id => 'present', $this->classmate->id => 'present'];

        $this->asTeacher()->putJson('/api/v1/teacher/sessions/' . $this->code('2026-09-25'), ['states' => $states])->assertOk();
        $this->asTeacher()->putJson('/api/v1/teacher/sessions/' . $this->code('2026-10-02'), ['states' => $states])->assertStatus(403);
    }

    public function test_every_student_and_only_valid_states_are_required()
    {
        $url = '/api/v1/teacher/sessions/' . $this->code('2026-09-18');

        $this->asTeacher()->putJson($url, ['states' => [$this->student->id => 'present']])->assertStatus(422);
        $this->asTeacher()->putJson($url, ['states' => [$this->student->id => 'present', 999999 => 'present']])
            ->assertStatus(422)
            ->assertJsonValidationErrors('states');
        $this->asTeacher()->putJson($url, ['states' => [$this->student->id => 'present', $this->classmate->id => 'attend']])
            ->assertStatus(422);
        $this->assertSame(0, Attends::count());
    }

    public function test_sessions_outside_my_scope_are_not_found()
    {
        $foreign = $this->enroll([], ['teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'x', 'user_id' => '999']]]);

        foreach ([
            $this->code('2026-09-18', '18:00', null, $this->otherSubject),   // another teacher's subject
            $this->code('2026-09-18', '16:00', $foreign),                     // another teacher's classroom
            $this->code('2026-09-17'),                                        // not a timetable day
            'not-a-code',
        ] as $code) {
            $this->asTeacher()->getJson('/api/v1/teacher/sessions/' . $code)->assertStatus(404);
        }
        $this->asTeacher()->getJson("/api/v1/teacher/classrooms/{$foreign->id}")->assertStatus(404);
    }

    public function test_stats_flag_students_who_need_follow_up()
    {
        foreach (['2026-09-11', '2026-09-18', '2026-09-25'] as $date) {
            $this->asTeacher()->putJson('/api/v1/teacher/sessions/' . $this->code($date), ['states' => [
                $this->student->id => 'present',
                $this->classmate->id => 'absent',
            ]])->assertOk();
        }

        $response = $this->asTeacher()->getJson("/api/v1/teacher/stats?classroom_id={$this->classroom->id}")->assertOk();
        $response->assertJsonPath('data.recorded', 6)
            ->assertJsonPath('data.rate', 50)
            ->assertJsonCount(1, 'data.follow_up')
            ->assertJsonPath('data.follow_up.0.id', $this->classmate->id)
            ->assertJsonPath('data.follow_up.0.absences', 3)
            ->assertJsonPath('data.classrooms.0.rate', 50);
    }
}
