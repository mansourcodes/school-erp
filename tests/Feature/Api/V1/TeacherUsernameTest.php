<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Teachers change their own sign-in username from the portal profile, confirming their current password. */
class TeacherUsernameTest extends TestCase
{
    use RefreshDatabase;

    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Teacher', 'web');
        $this->teacher = User::factory()->create(['username' => 'ahmad', 'email' => 'ahmad@example.com', 'password' => Hash::make('secret-pass')]);
        $this->teacher->assignRole('Teacher');
    }

    protected function change($username, $password = 'secret-pass')
    {
        return $this->actingAs($this->teacher, 'web')
            ->putJson('/api/v1/teacher/username', ['username' => $username, 'current_password' => $password]);
    }

    public function test_a_teacher_changes_their_username_and_signs_in_with_it()
    {
        $this->change('Ahmad.Sitri')
            ->assertOk()
            ->assertJsonPath('data.username', 'ahmad.sitri')
            ->assertJsonPath('data.email', 'ahmad@example.com');

        $this->assertSame('ahmad.sitri', $this->teacher->fresh()->username);
    }

    public function test_the_new_username_is_signed_in_with()
    {
        $this->teacher->update(['username' => 'ahmad.sitri']);

        $this->withHeaders(['Referer' => 'http://localhost/beta/login'])
            ->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => 'ahmad.sitri', 'password' => 'secret-pass'])
            ->assertOk()->assertJsonPath('data.username', 'ahmad.sitri');
    }

    public function test_the_username_is_validated()
    {
        User::factory()->create(['username' => 'taken']);

        $this->change('')->assertStatus(422)->assertJsonPath('errors.username.0', __('portal.username.required'));
        $this->change('ab')->assertStatus(422)->assertJsonPath('errors.username.0', __('portal.username.min'));
        $this->change(str_repeat('a', 51))->assertStatus(422)->assertJsonPath('errors.username.0', __('portal.username.max'));
        $this->change('أحمد')->assertStatus(422)->assertJsonPath('errors.username.0', __('portal.username.regex'));
        $this->change('ahmad sitri')->assertStatus(422)->assertJsonPath('errors.username.0', __('portal.username.regex'));
        $this->change('Taken')->assertStatus(422)->assertJsonPath('errors.username.0', __('portal.username.unique'));

        $this->assertSame('ahmad', $this->teacher->fresh()->username);
    }

    public function test_the_current_password_is_required_and_checked()
    {
        $this->change('ahmad.sitri', '')
            ->assertStatus(422)->assertJsonPath('errors.current_password.0', __('portal.password.current_required'));
        $this->change('ahmad.sitri', 'guess')
            ->assertStatus(422)->assertJsonPath('errors.current_password.0', __('portal.password.current_wrong'));

        $this->assertSame('ahmad', $this->teacher->fresh()->username);
    }

    public function test_keeping_the_same_username_is_allowed()
    {
        $this->change('ahmad')->assertOk()->assertJsonPath('data.username', 'ahmad');
    }

    public function test_changes_are_rate_limited()
    {
        // Wrong passwords count too, so the current password cannot be guessed through this form.
        for ($i = 0; $i < 10; $i++) {
            $this->change('ahmad' . $i, 'guess-' . $i)->assertStatus(422);
        }

        $this->change('ahmad-final')->assertStatus(429);
    }

    public function test_students_and_guests_cannot_use_it()
    {
        $this->putJson('/api/v1/teacher/username', [])->assertStatus(401);
        $this->actingAs(Student::factory()->create(), 'student')
            ->putJson('/api/v1/teacher/username', [])->assertStatus(403);
    }
}
