<?php

namespace Tests\Feature\Api\V1;

use App\Events\AttendanceFirstRecorded;
use App\Models\Attends;
use App\Models\PushDelivery;
use App\Models\Student;
use App\Models\StudentNotificationSetting;
use App\Models\User;
use App\Notifications\AttendanceRecordedPush;
use App\Notifications\MessageReceivedPush;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/** PLAN.md §5.12 / §11 P3.4: push on first attendance save and on messages; settings; subscriptions. */
class PushTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $teacher;

    protected $classroom;

    protected $classmate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        config(['webpush.vapid.public_key' => 'test-public', 'webpush.vapid.private_key' => 'test-private']);
        Carbon::setTestNow(Carbon::parse('2026-09-25 17:30', 'Asia/Bahrain')->utc());

        Role::findOrCreate('Teacher', 'web');
        $this->teacher = User::factory()->create();
        $this->teacher->assignRole('Teacher');
        $this->classroom = $this->enroll(['start_date' => '2026-09-04', 'end_date' => '2026-12-25'], [
            'teachers' => [['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => 'x', 'user_id' => (string) $this->teacher->id]],
            'attend_table' => [['curriculum_id' => (string) $this->curriculum->id, 'day' => '5', 'start_time' => '16:00']],
        ]);
        $this->classmate = Student::factory()->create();
        $this->classroom->students()->attach($this->classmate->id);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function saveRoster(array $states)
    {
        return $this->actingAs($this->teacher, 'web')->putJson(
            '/api/v1/teacher/sessions/' . urlencode('2026-09-18#16:00#' . $this->classroom->id . '#' . $this->curriculum->id),
            ['states' => $states]
        )->assertOk();
    }

    protected function subscribe(Student $student)
    {
        $student->updatePushSubscription('https://fcm.googleapis.com/fcm/send/' . $student->id, 'BKey', 'auth');
    }

    protected function sendDelivery($id, $user = null)
    {
        return $this->actingAs($user ?: $this->teacher, 'web')->postJson("/api/v1/teacher/push-deliveries/{$id}/send");
    }

    public function test_the_first_save_only_records_the_pushes_and_answers_at_once()
    {
        Notification::fake();
        $this->subscribe($this->student);
        $this->subscribe($this->classmate);

        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent'])
            ->assertJsonPath('data.push_pending', 2);

        Notification::assertNothingSent();
        $this->assertSame('present', PushDelivery::where('student_id', $this->student->id)->value('state'));
        $this->assertSame('absent', PushDelivery::where('student_id', $this->classmate->id)->value('state'));
    }

    public function test_the_teacher_app_sends_each_student_their_own_status_once()
    {
        $this->subscribe($this->student);
        $this->subscribe($this->classmate);
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);
        Notification::fake();

        $pending = $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/push-deliveries')->assertOk()->json('data');
        $this->assertCount(2, $pending);
        foreach ($pending as $row) {
            $this->sendDelivery($row['id'])->assertOk()->assertJsonPath('data.status', 'sent');
        }

        Notification::assertSentTo($this->student, AttendanceRecordedPush::class, function ($n) {
            return $n->state === 'present' && $n->date === '2026-09-18';
        });
        Notification::assertSentTo($this->classmate, AttendanceRecordedPush::class, function ($n) {
            return $n->state === 'absent';
        });

        // Sending again (a second tab, a retry) does nothing.
        $this->sendDelivery($pending[0]['id'])->assertOk()->assertJsonPath('data.status', 'sent');
        Notification::assertSentToTimes($this->student, AttendanceRecordedPush::class, 1);
        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/push-deliveries')->assertExactJson(['data' => []]);
    }

    public function test_only_students_with_a_device_and_the_type_on_get_a_row()
    {
        $this->subscribe($this->student);
        StudentNotificationSetting::create(['student_id' => $this->classmate->id, 'attendance' => false]);
        $this->subscribe($this->classmate);

        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent'])
            ->assertJsonPath('data.push_pending', 1);
        $this->assertSame([$this->student->id], PushDelivery::pluck('student_id')->all());
    }

    public function test_edits_never_notify_again()
    {
        $this->subscribe($this->student);
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);

        $this->saveRoster([$this->student->id => 'late', $this->classmate->id => 'absent_excused'])
            ->assertJsonPath('data.push_pending', 0);
        $this->assertSame(1, PushDelivery::count());
    }

    public function test_rows_are_the_teachers_own()
    {
        // Stored directly: one identity per test (Sanctum keeps the first resolved user).
        $id = PushDelivery::create([
            'user_id' => $this->teacher->id, 'student_id' => $this->student->id, 'type' => 'messages', 'message_id' => 1,
        ])->id;

        $other = User::factory()->create();
        $other->assignRole('Teacher');
        $this->sendDelivery($id, $other)->assertStatus(404);
        $this->assertSame(PushDelivery::PENDING, PushDelivery::first()->status);
    }

    public function test_students_cannot_reach_the_outbox()
    {
        $this->actingAs($this->student, 'student')->getJson('/api/v1/teacher/push-deliveries')->assertStatus(403);
    }

    public function test_unsent_rows_expire_after_a_day()
    {
        $this->subscribe($this->student);
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);

        Carbon::setTestNow(now()->addHours(25));
        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/push-deliveries')->assertExactJson(['data' => []]);
        // The poll only reads; the scheduled tidy records it.
        $this->assertSame(PushDelivery::PENDING, PushDelivery::first()->status);

        $this->artisan('push:outbox-tidy')->assertExitCode(0);
        $this->assertSame(PushDelivery::SKIPPED, PushDelivery::first()->status);
    }

    /** No scheduler cron in production: the first admin dashboard visit of the day tidies the outbox. */
    public function test_the_admin_dashboard_tidies_the_outbox_once_per_day()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
        $this->subscribe($this->student);
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);

        $this->travel(25)->hours();
        $this->actingAs($admin, 'backpack')->get('/admin/dashboard')->assertOk();
        $this->assertSame(PushDelivery::SKIPPED, PushDelivery::first()->status);
    }

    public function test_a_send_that_never_finished_is_offered_again_after_five_minutes()
    {
        $this->subscribe($this->student);
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);
        $delivery = PushDelivery::first();
        // The request died mid-send.
        $delivery->forceFill(['status' => PushDelivery::SENDING, 'attempts' => 1])->save();
        Notification::fake();

        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/push-deliveries')->assertExactJson(['data' => []]);
        $this->sendDelivery($delivery->id)->assertOk()->assertJsonPath('data.status', 'sending');

        Carbon::setTestNow(now()->addMinutes(6));
        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/push-deliveries')->assertJsonCount(1, 'data');
        $this->assertSame(PushDelivery::SENDING, $delivery->fresh()->status);
        $this->sendDelivery($delivery->id)->assertOk()->assertJsonPath('data.status', 'sent');
        Notification::assertSentToTimes($this->student, AttendanceRecordedPush::class, 1);
    }

    public function test_a_stuck_send_out_of_attempts_is_not_offered_again()
    {
        $this->subscribe($this->student);
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);
        $delivery = PushDelivery::first();
        $delivery->forceFill(['status' => PushDelivery::SENDING, 'attempts' => 3])->save();

        Carbon::setTestNow(now()->addMinutes(6));
        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/push-deliveries')->assertExactJson(['data' => []]);

        $this->artisan('push:outbox-tidy');
        $this->assertSame(PushDelivery::FAILED, $delivery->fresh()->status);
    }

    public function test_an_admin_save_sends_at_once()
    {
        Notification::fake();
        $this->subscribe($this->student);
        $attend = Attends::create([
            'date' => '2026-09-18', 'start_time' => '16:00', 'class_room_id' => $this->classroom->id,
            'course_id' => $this->classroom->course_id, 'curriculum_id' => $this->curriculum->id,
        ]);
        app(\App\Services\Attendance\AttendanceRecorder::class)->syncStates($attend, [$this->student->id => 'attend']);

        event(new AttendanceFirstRecorded($attend->id));

        Notification::assertSentTo($this->student, AttendanceRecordedPush::class);
        $this->assertSame(PushDelivery::SENT, PushDelivery::first()->status);
    }

    public function test_a_message_records_its_recipients_only()
    {
        $this->subscribe($this->student);
        $this->subscribe($this->classmate);

        $this->actingAs($this->teacher, 'web')->postJson('/api/v1/teacher/messages', [
            'classroom_id' => $this->classroom->id,
            'student_ids' => [$this->student->id],
            'body' => 'غدًا اختبار قصير في أول عشر دقائق من الحصة.',
        ])->assertStatus(201)->assertJsonPath('data.push_pending', 1);

        Notification::fake();
        $this->sendDelivery(PushDelivery::first()->id)->assertJsonPath('data.status', 'sent');
        Notification::assertSentTo($this->student, MessageReceivedPush::class);
        Notification::assertNotSentTo($this->classmate, MessageReceivedPush::class);
    }

    public function test_payloads_are_arabic_rtl_and_open_the_right_screen()
    {
        $attendance = (new AttendanceRecordedPush('absent', 'تجويد القرآن الكريم', '2026-09-18', '16:00:00', 7))
            ->toWebPush($this->student, null)->toArray();
        $this->assertSame('تم تسجيلك غائبًا ✗', $attendance['title']);
        $this->assertStringContainsString('تجويد القرآن الكريم', $attendance['body']);
        $this->assertStringContainsString('إن كان لديك عذر راجع الإدارة', $attendance['body']);
        $this->assertSame('rtl', $attendance['dir']);
        $this->assertSame('attendance-7', $attendance['tag']);
        $this->assertSame(['url' => '/beta/attendance?date=2026-09-18'], $attendance['data']);

        // Titled with the centre name from Backpack Settings (`portal.centre`).
        config(['settings.portal.centre' => 'مركز النور']);
        $message = (new MessageReceivedPush(42, str_repeat('نص ', 60)))->toWebPush($this->student, null)->toArray();
        $this->assertSame('مركز النور', $message['title']);
        $this->assertLessThanOrEqual(83, mb_strlen($message['body']));
        $this->assertSame(['url' => '/beta/messages#42'], $message['data']);

        // Once the panel is live it is at the root, so the tap opens the screen without /beta.
        config(['settings.portal.mode' => 'live']);
        $this->assertSame(['url' => '/messages#42'], (new MessageReceivedPush(42, 'نص'))->toWebPush($this->student, null)->toArray()['data']);
        // No CPR in a push payload (PLAN.md §9.3).
        $this->assertStringNotContainsString((string) $this->student->cpr, json_encode($attendance + $message));
    }

    public function test_settings_switch_each_type_off_server_side()
    {
        $push = new MessageReceivedPush(1, 'x');
        $this->assertSame([WebPushChannel::class], $push->via($this->student));

        $this->actingAs($this->student, 'student')
            ->patchJson('/api/v1/student/notification-settings', ['messages' => false])
            ->assertOk()
            ->assertExactJson(['data' => ['attendance' => true, 'messages' => false]]);

        $this->assertSame([], $push->via($this->student->fresh()));
        $this->assertSame([WebPushChannel::class], (new AttendanceRecordedPush('present', 'x', '2026-09-18', null, 1))->via($this->student));
    }

    public function test_nothing_is_sent_without_vapid_keys()
    {
        config(['webpush.vapid.public_key' => null]);

        $this->assertSame([], (new MessageReceivedPush(1, 'x'))->via($this->student));
    }

    public function test_a_device_subscribes_and_unsubscribes_on_logout()
    {
        $endpoint = 'https://fcm.googleapis.com/fcm/send/abc123';

        $this->actingAs($this->student, 'student')->postJson('/api/v1/push/subscriptions', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => 'BPublicKey', 'auth' => 'authToken'],
        ])->assertNoContent();
        $this->assertSame(1, $this->student->pushSubscriptions()->count());

        $this->actingAs($this->student, 'student')->deleteJson('/api/v1/push/subscriptions', ['endpoint' => $endpoint])->assertNoContent();
        $this->assertSame(0, $this->student->pushSubscriptions()->count());
    }

    public function test_only_https_endpoints_and_only_students()
    {
        $this->actingAs($this->student, 'student')->postJson('/api/v1/push/subscriptions', [
            'endpoint' => 'http://evil.example/x',
            'keys' => ['p256dh' => 'k', 'auth' => 'a'],
        ])->assertStatus(422);
    }

    public function test_teachers_get_no_push_endpoints()
    {
        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/push/config')->assertStatus(403);
    }
}
