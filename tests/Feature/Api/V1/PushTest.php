<?php

namespace Tests\Feature\Api\V1;

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

    public function test_the_first_save_notifies_each_student_with_their_own_status()
    {
        Notification::fake();

        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);

        Notification::assertSentTo($this->student, AttendanceRecordedPush::class, function ($n) {
            return $n->state === 'present' && $n->date === '2026-09-18';
        });
        Notification::assertSentTo($this->classmate, AttendanceRecordedPush::class, function ($n) {
            return $n->state === 'absent';
        });
    }

    public function test_edits_never_notify_again()
    {
        $this->saveRoster([$this->student->id => 'present', $this->classmate->id => 'absent']);
        Notification::fake();

        $this->saveRoster([$this->student->id => 'late', $this->classmate->id => 'absent_excused']);

        Notification::assertNothingSent();
    }

    public function test_a_message_notifies_its_recipients_only()
    {
        Notification::fake();

        $this->actingAs($this->teacher, 'web')->postJson('/api/v1/teacher/messages', [
            'classroom_id' => $this->classroom->id,
            'student_ids' => [$this->student->id],
            'body' => 'غدًا اختبار قصير في أول عشر دقائق من الحصة.',
        ])->assertStatus(201);

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

        $message = (new MessageReceivedPush(42, str_repeat('نص ', 60)))->toWebPush($this->student, null)->toArray();
        $this->assertSame('جمعية الغدير لعلوم القرآن', $message['title']);
        $this->assertLessThanOrEqual(83, mb_strlen($message['body']));
        $this->assertSame(['url' => '/beta/messages#42'], $message['data']);
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
