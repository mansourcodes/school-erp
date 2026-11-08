<?php

namespace Tests\Feature\Admin;

use App\Models\Attends;
use App\Models\ClassRoom;
use App\Models\Course;
use App\Models\Curriculum;
use App\Models\Student;
use App\Models\User;
use App\Support\AdminDashboard;
use App\Support\QuranQuotes;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Admin dashboard (base/dashboard): greeting, Quran verse card, Superadmin figures and shortcuts. */
class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function userWithRole($role)
    {
        $user = User::factory()->create(['name' => 'منصور']);
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    public function test_figures_count_only_active_courses()
    {
        $active = Course::factory()->create(['is_active' => true]);
        $old = Course::factory()->create(['is_active' => false]);
        $current = ClassRoom::factory()->create(['course_id' => $active->id]);
        $archived = ClassRoom::factory()->create(['course_id' => $old->id]);

        $students = Student::factory()->count(3)->create();
        $current->students()->attach($students->take(2)->pluck('id'));
        $archived->students()->attach($students->pluck('id'));

        $this->assertSame([
            'active_courses' => 1,
            'active_classrooms' => 1,
            'active_students' => 2,
            'students' => 3,
        ], AdminDashboard::stats());
    }

    public function test_the_attendance_chart_counts_active_courses_only()
    {
        $current = ClassRoom::factory()->create(['course_id' => Course::factory()->create(['is_active' => true])->id]);
        $archived = ClassRoom::factory()->create(['course_id' => Course::factory()->create(['is_active' => false])->id]);
        $students = Student::factory()->count(4)->create()->pluck('id');
        $curriculum = Curriculum::factory()->create()->id;

        $session = Attends::create(['date' => '2026-10-01', 'start_time' => '16:00', 'class_room_id' => $current->id, 'course_id' => $current->course_id, 'curriculum_id' => $curriculum]);
        $session->attendStudents()->attach($students->take(2));
        $session->lateStudents()->attach($students[2]);
        $session->absentStudents()->attach($students[3]);

        $old = Attends::create(['date' => '2025-10-01', 'start_time' => '16:00', 'class_room_id' => $archived->id, 'course_id' => $archived->course_id, 'curriculum_id' => $curriculum]);
        $old->absentStudents()->attach($students);

        $attendance = AdminDashboard::attendance();
        $counts = collect($attendance['slices'])->pluck('count', 'key')->all();

        $this->assertSame(4, $attendance['total']);
        $this->assertSame('75%', $attendance['centre']); // present + late
        $this->assertSame(['student_attended' => 2, 'student_late' => 1, 'student_late_w_excuse' => 0,
            'student_absent_w_excuse' => 0, 'student_absent' => 1, 'unrecorded' => 0], $counts); // no timetable here
        $this->assertSame(['present', 'late', 'late-excused', 'absent-excused', 'absent', 'muted'],
            collect($attendance['slices'])->pluck('slot')->all());
    }

    public function test_the_classroom_chart_counts_timetable_sessions_up_to_today()
    {
        Carbon::setTestNow('2026-10-20 12:00:00'); // Thursdays so far: 10-01, 10-08, 10-15
        $dates = ['start_date' => '2026-10-01', 'end_date' => '2026-10-31'];
        $course = Course::factory()->create(['is_active' => true] + $dates);
        $curriculum = Curriculum::factory()->create()->id;
        $room = function ($course, $startTime) use ($curriculum) {
            return ClassRoom::factory()->create(['course_id' => $course->id,
                'attend_table' => [['day' => '4', 'start_time' => $startTime, 'curriculum_id' => (string) $curriculum]]]);
        };
        $record = function (ClassRoom $classroom, $date, $time) use ($curriculum) {
            Attends::create(['date' => $date, 'start_time' => $time, 'class_room_id' => $classroom->id,
                'course_id' => $classroom->course_id, 'curriculum_id' => $curriculum]);
        };

        $timed = $room($course, '16:00');
        $untimed = $room($course, null);
        $archived = $room(Course::factory()->create(['is_active' => false] + $dates), '16:00');

        $record($timed, '2026-10-01', '16:00');
        $record($timed, '2026-10-08', '16:30');  // same hour counts
        $record($timed, '2026-10-15', '18:00');  // another hour: not this period
        $record($timed, '2026-10-29', '16:00');  // after today: outside the total
        $record($untimed, '2026-10-08', '09:12'); // a period without a time matches any record that day
        $record($archived, '2026-10-01', '16:00');

        $chart = AdminDashboard::classrooms();

        $this->assertSame('3 / 6', $chart['centre']);
        $this->assertSame(['recorded' => 3, 'not_recorded' => 3], collect($chart['slices'])->pluck('count', 'key')->all());
        Carbon::setTestNow();
    }

    public function test_the_attendance_chart_adds_unrecorded_student_sessions_outside_the_rate()
    {
        Carbon::setTestNow('2026-10-20 12:00:00'); // Thursdays so far: 10-01, 10-08, 10-15
        $course = Course::factory()->create(['is_active' => true, 'start_date' => '2026-10-01', 'end_date' => '2026-10-31']);
        $curriculum = Curriculum::factory()->create()->id;
        $classroom = ClassRoom::factory()->create(['course_id' => $course->id,
            'attend_table' => [['day' => '4', 'start_time' => '16:00', 'curriculum_id' => (string) $curriculum]]]);
        $students = Student::factory()->count(3)->create()->pluck('id');
        $classroom->students()->attach($students);
        $record = function ($date) use ($classroom, $curriculum) {
            return Attends::create(['date' => $date, 'start_time' => '16:00', 'class_room_id' => $classroom->id,
                'course_id' => $classroom->course_id, 'curriculum_id' => $curriculum]);
        };

        $full = $record('2026-10-01');
        $full->attendStudents()->attach($students->take(2));
        $full->absentStudents()->attach($students[2]);
        $record('2026-10-08')->lateStudents()->attach($students[0]); // two students left out
        // 10-15: never recorded, all three students are unrecorded

        $chart = AdminDashboard::attendance();
        $counts = collect($chart['slices'])->pluck('count', 'key')->all();

        $this->assertSame(9, $chart['total']); // 3 sessions × 3 students
        $this->assertSame(['student_attended' => 2, 'student_late' => 1, 'student_late_w_excuse' => 0,
            'student_absent_w_excuse' => 0, 'student_absent' => 1, 'unrecorded' => 5], $counts);
        $this->assertSame('75%', $chart['centre']); // 3 attended of 4 recorded; unrecorded stays out
        $this->assertSame(['muted', '—'], [end($chart['slices'])['slot'], end($chart['slices'])['code']]);
        Carbon::setTestNow();
    }

    public function test_teachers_are_ranked_by_their_active_classrooms_sessions()
    {
        $course = Course::factory()->create(['is_active' => true]);
        $old = Course::factory()->create(['is_active' => false]);
        $curriculum = Curriculum::factory()->create()->id;
        $other = Curriculum::factory()->create()->id;
        $session = function (ClassRoom $classroom, $count, $curriculumId = null) use ($curriculum) {
            for ($day = 1; $day <= $count; $day++) {
                Attends::create(['date' => '2026-10-'.str_pad($day, 2, '0', STR_PAD_LEFT), 'start_time' => '16:00',
                    'class_room_id' => $classroom->id, 'course_id' => $classroom->course_id,
                    'curriculum_id' => $curriculumId ?: $curriculum]);
            }
        };

        $teachers = collect(['أ', 'ب', 'ج', 'د'])->map(function ($name) {
            $user = $this->userWithRole('Teacher');
            $user->update(['name' => $name]);

            return $user;
        });
        // Assignments live in the classroom's `teachers` JSON, user_id as a string like Backpack saves it.
        $room = function ($course, array $assignments) {
            return ClassRoom::factory()->create(['course_id' => $course->id, 'teachers' => array_map(function ($a) {
                return ['user_id' => (string) $a[0]->id, 'curriculum_id' => (string) $a[1], 'teacher_name' => $a[0]->name];
            }, $assignments)]);
        };
        $rooms = [
            $room($course, [[$teachers[0], $curriculum]]),
            $room($course, [[$teachers[1], $curriculum], [$teachers[3], $other]]),
            $room($course, [[$teachers[2], $curriculum]]),
        ];
        $archived = $room($old, [[$teachers[3], $curriculum]]); // an old course does not count

        $session($rooms[0], 5);
        $session($rooms[1], 3); // ب's subject only, not د's
        $session($rooms[2], 1);
        $session($archived, 9);

        $ranking = AdminDashboard::teachers(3);
        $names = function ($rows) {
            return collect($rows)->map(function ($row) { return $row['name'].':'.$row['sessions']; })->all();
        };

        $this->assertSame(['أ:5', 'ب:3', 'ج:1'], $names($ranking['most']));
        $this->assertSame(['د:0'], $names($ranking['least'])); // the others are already in "most"
    }

    public function test_the_superadmin_sees_a_verse_the_figures_and_the_shortcuts()
    {
        $response = $this->actingAs($this->userWithRole('Superadmin'), 'backpack')
            ->get(backpack_url('dashboard'))->assertOk()
            ->assertSee('السلام عليكم، منصور')
            ->assertSee('class="dash-quran', false)
            ->assertSee(__('base.dashboard.active_students'))
            ->assertSee(__('base.dashboard.attendance_empty'))
            ->assertSee(__('base.dashboard.classrooms_empty'))
            ->assertSee(__('base.dashboard.teachers_empty'))
            ->assertSee(backpack_url('payment'), false);

        $quote = $response->viewData('quote');
        $this->assertContains($quote, QuranQuotes::all());
        $response->assertSee($quote['text']);
    }

    public function test_the_figures_are_cached_for_four_days_until_the_refresh_button()
    {
        $admin = $this->userWithRole('Superadmin');
        Student::factory()->create();

        $this->actingAs($admin, 'backpack')->get(backpack_url('dashboard'))->assertOk();
        $this->assertSame(1, AdminDashboard::cached()['stats']['students']);

        Student::factory()->create();
        $this->assertSame(1, AdminDashboard::cached()['stats']['students'], 'still the cached figure');

        $this->travel(AdminDashboard::CACHE_DAYS)->days();
        $this->travel(1)->minutes();
        $this->assertSame(2, AdminDashboard::cached()['stats']['students'], 'expired after four days');

        Student::factory()->create();
        $this->actingAs($admin, 'backpack')->post(route('admin.dashboard.refresh'))
            ->assertRedirect(route('backpack.dashboard'));
        $this->assertSame(3, AdminDashboard::cached()['stats']['students'], 'recomputed after the button');
    }

    public function test_the_dashboard_shows_the_refresh_button()
    {
        $this->actingAs($this->userWithRole('Superadmin'), 'backpack')
            ->get(backpack_url('dashboard'))->assertOk()
            ->assertSee(route('admin.dashboard.refresh'), false)
            ->assertSee(__('base.dashboard.refresh'));
    }

    public function test_long_verses_get_a_smaller_text_size()
    {
        $this->assertSame('lg', QuranQuotes::size('لَئِن شَكَرْتُمْ لَأَزِيدَنَّكُمْ'));
        $this->assertSame('md', QuranQuotes::size('فَلَا تَعْلَمُ نَفْسٌ مَّا أُخْفِيَ لَهُم مِّن قُرَّةِ أَعْيُنٍ جَزَاءً بِمَا كَانُوا يَعْمَلُونَ'));
        $this->assertSame('sm', QuranQuotes::size(collect(QuranQuotes::ALL)->firstWhere('ayah', '261')['text']));

        foreach (QuranQuotes::all() as $quote) {
            $this->assertContains($quote['size'], ['lg', 'md', 'sm']);
        }
    }

    public function test_a_teacher_still_goes_to_the_attendance_page()
    {
        $this->actingAs($this->userWithRole('Teacher'), 'backpack')
            ->get(backpack_url('dashboard'))
            ->assertRedirect(backpack_url('add_attend_by_date'));
    }
}
