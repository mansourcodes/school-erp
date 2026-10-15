<?php

namespace Tests\Feature\Api\V1;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/** PLAN.md §5.11 / §11 P3.3: teacher → selected students, one-way, with every scope check. */
class MessagesTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $teacher;

    protected $classroom;

    protected $classmate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        Role::findOrCreate('Teacher', 'web');
        $this->teacher = $this->makeTeacher();
        $this->classroom = $this->enroll([], ['teachers' => [$this->assignment($this->teacher)]]);
        $this->classmate = Student::factory()->create();
        $this->classroom->students()->attach($this->classmate->id);
    }

    protected function makeTeacher()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');

        return $teacher;
    }

    protected function assignment(User $teacher)
    {
        return ['curriculum_id' => (string) $this->curriculum->id, 'teacher_name' => $teacher->name, 'user_id' => (string) $teacher->id];
    }

    /**
     * A stored message, without going through the API: tests that act as a second identity must
     * not make requests as the teacher first (Sanctum keeps the resolved user for the app's
     * lifetime, and all requests of a PHPUnit test share one app; real requests do not).
     */
    protected function stored(array $studentIds, $body = 'رسالة')
    {
        $message = Message::create(['sender_user_id' => $this->teacher->id, 'class_room_id' => $this->classroom->id, 'body' => $body]);
        foreach ($studentIds as $id) {
            $message->recipients()->create(['student_id' => $id]);
        }

        return $message;
    }

    protected function send(array $overrides = [], $teacher = null)
    {
        return $this->actingAs($teacher ?: $this->teacher, 'web')->postJson('/api/v1/teacher/messages', array_merge([
            'classroom_id' => $this->classroom->id,
            'student_ids' => [$this->student->id],
            'body' => 'يرجى مراجعة الدرس الثالث قبل الاختبار.',
        ], $overrides));
    }

    public function test_a_teacher_sends_to_selected_students_of_their_classroom()
    {
        Event::fake([MessageSent::class]);

        $this->send()->assertStatus(201)->assertJsonPath('data.recipients_count', 1);

        $message = Message::first();
        $this->assertSame([$this->student->id], $message->recipients()->pluck('student_id')->all());
        Event::assertDispatched(MessageSent::class);
    }

    public function test_body_is_required_and_at_most_500_characters()
    {
        $this->send(['body' => '   '])->assertStatus(422)->assertJsonValidationErrors('body');
        $this->send(['body' => str_repeat('ب', 501)])->assertStatus(422)->assertJsonValidationErrors('body');
        $this->send(['body' => str_repeat('ب', 500)])->assertStatus(201);
    }

    public function test_only_my_current_classrooms_and_their_students()
    {
        $other = $this->enroll([], ['teachers' => [$this->assignment($this->makeTeacher())]]);
        $archived = $this->enroll(['is_active' => 0], ['teachers' => [$this->assignment($this->teacher)]]);
        $stranger = Student::factory()->create();

        $this->send(['classroom_id' => $other->id])->assertStatus(404);
        $this->send(['classroom_id' => $archived->id])->assertStatus(404);
        $this->send(['student_ids' => [$this->student->id, $stranger->id]])->assertStatus(422)->assertJsonValidationErrors('student_ids');
        $this->assertSame(0, Message::count());
    }

    public function test_students_cannot_send()
    {
        $this->actingAs($this->student, 'student')->postJson('/api/v1/teacher/messages', ['body' => 'x'])->assertStatus(403);
    }

    public function test_sent_list_shows_read_progress()
    {
        $this->send(['student_ids' => [$this->student->id, $this->classmate->id]]);
        Message::first()->recipients()->where('student_id', $this->student->id)->update(['read_at' => now()]);

        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/messages')
            ->assertOk()
            ->assertJsonPath('data.0.recipients_count', 2)
            ->assertJsonPath('data.0.read_count', 1)
            ->assertJsonPath('data.0.classroom.id', $this->classroom->id);
    }

    public function test_a_teacher_deletes_only_their_own_messages()
    {
        $message = $this->stored([$this->student->id]);

        $this->actingAs($this->makeTeacher(), 'web')->deleteJson("/api/v1/teacher/messages/{$message->id}")->assertStatus(404);
        $this->assertNull($message->fresh()->deleted_at);
    }

    public function test_a_teacher_edits_their_own_message_and_read_state_stays()
    {
        $message = $this->stored([$this->student->id]);
        $message->recipients()->update(['read_at' => now()]);
        $this->travel(5)->minutes();

        $this->actingAs($this->teacher, 'web')->patchJson("/api/v1/teacher/messages/{$message->id}", ['body' => '  نص مصحح  '])
            ->assertOk()
            ->assertJsonPath('data.body', 'نص مصحح')
            ->assertJsonPath('data.edited_at', now()->toIso8601String());

        // Recipients and their read state stay as sent.
        $this->assertSame('نص مصحح', $message->fresh()->body);
        $this->assertNotNull($message->recipients()->first()->read_at);
    }

    /** Separate from the edit request: Sanctum keeps the teacher for the rest of a test (see stored()). */
    public function test_the_inbox_shows_an_edited_message_as_edited()
    {
        $message = $this->stored([$this->student->id]);
        $this->travel(5)->minutes();
        $message->update(['body' => 'نص مصحح']);

        $this->actingAs($this->student, 'student')->getJson('/api/v1/student/messages')
            ->assertJsonPath('data.0.body', 'نص مصحح')
            ->assertJsonPath('data.0.edited_at', now()->toIso8601String());
    }

    public function test_an_unedited_message_has_no_edited_time()
    {
        $this->stored([$this->student->id]);

        $this->actingAs($this->teacher, 'web')->getJson('/api/v1/teacher/messages')->assertJsonPath('data.0.edited_at', null);
    }

    public function test_editing_checks_the_owner_and_the_text()
    {
        $message = $this->stored([$this->student->id]);

        $this->actingAs($this->teacher, 'web')->patchJson("/api/v1/teacher/messages/{$message->id}", ['body' => '   '])->assertStatus(422);
        $this->actingAs($this->teacher, 'web')
            ->patchJson("/api/v1/teacher/messages/{$message->id}", ['body' => str_repeat('ا', 501)])->assertStatus(422);
        $message->delete();
        $this->actingAs($this->teacher, 'web')->patchJson("/api/v1/teacher/messages/{$message->id}", ['body' => 'جديد'])->assertStatus(404);
        $this->assertSame('رسالة', Message::withTrashed()->find($message->id)->body);
    }

    public function test_a_teacher_cannot_edit_another_teachers_message()
    {
        $message = $this->stored([$this->student->id]);

        $this->actingAs($this->makeTeacher(), 'web')->patchJson("/api/v1/teacher/messages/{$message->id}", ['body' => 'جديد'])->assertStatus(404);
        $this->assertSame('رسالة', $message->fresh()->body);
    }

    public function test_deleting_hides_the_message_from_the_inbox()
    {
        $this->send();
        $message = Message::first();
        $this->actingAs($this->teacher, 'web')->deleteJson("/api/v1/teacher/messages/{$message->id}")->assertNoContent();

        $this->assertSoftDeleted($message);
    }

    public function test_the_inbox_lists_my_messages_and_reading_is_idempotent()
    {
        $mine = $this->stored([$this->student->id]);
        $this->stored([$this->classmate->id], 'لغيري');

        $this->actingAs($this->student, 'student')->getJson('/api/v1/student/messages')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $mine->id)
            ->assertJsonPath('data.0.teacher', $this->teacher->name)
            ->assertJsonPath('data.0.read_at', null);

        $first = $this->actingAs($this->student, 'student')->postJson("/api/v1/student/messages/{$mine->id}/read")->assertOk()->json('data.read_at');
        $second = $this->actingAs($this->student, 'student')->postJson("/api/v1/student/messages/{$mine->id}/read")->json('data.read_at');
        $this->assertSame($first, $second);
    }

    public function test_reading_a_deleted_or_foreign_message_is_not_found_and_the_badge_counts_unread()
    {
        $kept = $this->stored([$this->student->id]);
        $deleted = $this->stored([$this->student->id]);
        $foreign = $this->stored([$this->classmate->id]);
        $deleted->delete();

        $this->actingAs($this->student, 'student')->postJson("/api/v1/student/messages/{$deleted->id}/read")->assertStatus(404);
        $this->actingAs($this->student, 'student')->postJson("/api/v1/student/messages/{$foreign->id}/read")->assertStatus(404);
        $this->actingAs($this->student, 'student')->getJson('/api/v1/me')->assertJsonPath('data.unread_messages', 1);
        $this->assertNotNull($kept);
    }

    public function test_messages_older_than_90_days_are_pruned()
    {
        $this->stored([$this->student->id]);
        $this->stored([$this->student->id]);
        Message::orderBy('id')->first()->forceFill(['created_at' => now()->subDays(91)])->save();

        $this->artisan('messages:prune')->assertExitCode(0);

        $this->assertSame(1, Message::count());
        $this->assertSame(1, Message::onlyTrashed()->count());
    }

    /** No scheduler cron in production: the first admin dashboard visit of the day prunes. */
    public function test_the_admin_dashboard_prunes_once_per_day()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
        $this->stored([$this->student->id])->forceFill(['created_at' => now()->subDays(91)])->save();

        $this->actingAs($admin, 'backpack')->get('/admin/dashboard')->assertOk();
        $this->assertSame(0, Message::count());

        // Same day: a second old message stays until tomorrow's first visit.
        $this->stored([$this->student->id])->forceFill(['created_at' => now()->subDays(91)])->save();
        $this->actingAs($admin, 'backpack')->get('/admin/dashboard')->assertOk();
        $this->assertSame(1, Message::count());

        $this->travel(1)->days();
        $this->actingAs($admin, 'backpack')->get('/admin/dashboard')->assertOk();
        $this->assertSame(0, Message::count());
    }
}
