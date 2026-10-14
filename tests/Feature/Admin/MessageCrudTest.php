<?php

namespace Tests\Feature\Admin;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Role;
use Tests\Feature\Api\V1\Concerns\BuildsEnrollments;
use Tests\TestCase;

/** /admin/message: the Superadmin reads, sends, edits and deletes any message. */
class MessageCrudTest extends TestCase
{
    use RefreshDatabase, BuildsEnrollments;

    protected $admin;

    protected $classroom;

    protected $classmate;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpEnrollments();
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
        $this->classroom = $this->enroll();
        $this->classmate = Student::factory()->create();
        $this->classroom->students()->attach($this->classmate->id);
    }

    protected function stored($body = 'رسالة')
    {
        $teacher = User::factory()->create();
        $message = Message::create(['sender_user_id' => $teacher->id, 'class_room_id' => $this->classroom->id, 'body' => $body]);
        $message->recipients()->create(['student_id' => $this->student->id]);

        return $message;
    }

    /**
     * Backpack's CrudPanel is a singleton and every request of one test shares the app, so the
     * list and show pages (different columns) each get their own test; real requests do not share.
     */
    public function test_the_admin_lists_any_message()
    {
        $this->stored('مراجعة الدرس الثالث');

        $this->actingAs($this->admin, 'backpack')->get('/admin/message')->assertOk();
        $rows = $this->actingAs($this->admin, 'backpack')
            ->postJson('/admin/message/search', [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->assertJsonPath('recordsTotal', 1)->json('data');
        $this->assertStringContainsString('0 / 1', $rows[0][4]); // read / recipients
    }

    public function test_the_admin_sees_a_message_with_its_recipients()
    {
        $message = $this->stored('مراجعة الدرس الثالث');

        $this->actingAs($this->admin, 'backpack')->get("/admin/message/{$message->id}/show")
            ->assertOk()->assertSee('مراجعة الدرس الثالث')->assertSee($this->student->name);
    }

    public function test_the_admin_sends_to_chosen_students_of_a_classroom()
    {
        Event::fake([MessageSent::class]);

        $this->actingAs($this->admin, 'backpack')->post('/admin/message', [
            'class_room_id' => $this->classroom->id,
            'student_ids' => [$this->classmate->id],
            'body' => '  تذكير بالاختبار  ',
        ])->assertRedirect();

        $message = Message::with('recipients')->firstOrFail();
        $this->assertSame($this->admin->id, $message->sender_user_id);
        $this->assertSame('تذكير بالاختبار', $message->body);
        $this->assertSame([$this->classmate->id], $message->recipients->pluck('student_id')->all());
        Event::assertDispatched(MessageSent::class, function ($e) {
            return $e->sendNow === true;
        });
    }

    public function test_the_admin_sends_to_the_whole_classroom()
    {
        Event::fake([MessageSent::class]);

        $this->actingAs($this->admin, 'backpack')->post('/admin/message', [
            'class_room_id' => $this->classroom->id,
            'all_students' => '1',
            'body' => 'للجميع',
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(
            [$this->student->id, $this->classmate->id],
            Message::firstOrFail()->recipients()->pluck('student_id')->all()
        );
    }

    public function test_students_outside_the_classroom_are_refused()
    {
        $outsider = Student::factory()->create();

        $this->actingAs($this->admin, 'backpack')->post('/admin/message', [
            'class_room_id' => $this->classroom->id,
            'student_ids' => [$outsider->id],
            'body' => 'رسالة',
        ])->assertSessionHasErrors('student_ids');

        $this->assertSame(0, Message::count());
    }

    public function test_the_admin_edits_the_text_and_deletes_a_message()
    {
        $message = $this->stored();

        $this->actingAs($this->admin, 'backpack')->put('/admin/message/' . $message->id, [
            'id' => $message->id,
            'body' => 'نص مصحح',
        ])->assertRedirect();
        $this->assertSame('نص مصحح', $message->fresh()->body);
        $this->assertSame(1, $message->recipients()->count());

        $this->actingAs($this->admin, 'backpack')->delete('/admin/message/' . $message->id)->assertOk();
        $this->assertSame(0, Message::count());
        $this->assertSame(1, Message::onlyTrashed()->count());
    }

    public function test_a_teacher_cannot_open_it()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole(Role::findOrCreate('Teacher', 'web'));

        $this->actingAs($teacher, 'backpack')->get('/admin/message')->assertRedirect();
        $this->assertSame(0, Message::count());
    }
}
