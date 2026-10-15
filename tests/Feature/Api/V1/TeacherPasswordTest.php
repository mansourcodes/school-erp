<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Teachers change their own password from the portal profile after confirming the current one. */
class TeacherPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Teacher', 'web');
        $this->teacher = User::factory()->create(['password' => Hash::make('old-secret')]);
        $this->teacher->assignRole('Teacher');
    }

    protected function change(array $body)
    {
        // From the SPA's origin, like the real app: Sanctum then starts the session the controller regenerates.
        return $this->actingAs($this->teacher, 'web')
            ->withHeaders(['Referer' => 'http://localhost/beta/teacher/profile'])
            ->putJson('/api/v1/teacher/password', $body + [
            'current_password' => 'old-secret',
            'password' => 'new-secret-1',
            'password_confirmation' => 'new-secret-1',
        ]);
    }

    public function test_a_teacher_changes_their_password_and_other_devices_sign_out()
    {
        $token = $this->teacher->remember_token;
        $recaller = Auth::guard('web')->getRecallerName();

        $response = $this->change([])->assertNoContent();

        $fresh = $this->teacher->fresh();
        $this->assertTrue(Hash::check('new-secret-1', $fresh->password));
        // A new remember token signs the other devices out; this device gets a fresh remember cookie.
        $this->assertNotSame($token, $fresh->remember_token);
        $this->assertTrue(collect($response->headers->getCookies())->contains(function ($cookie) use ($recaller) {
            return $cookie->getName() === $recaller && ! $cookie->isCleared();
        }));
    }

    public function test_a_wrong_current_password_is_rejected()
    {
        $this->change(['current_password' => 'guess'])
            ->assertStatus(422)
            ->assertJsonPath('errors.current_password.0', __('portal.password.current_wrong'));

        $this->assertTrue(Hash::check('old-secret', $this->teacher->fresh()->password));
    }

    public function test_the_new_password_is_validated()
    {
        $this->change(['password' => 'short', 'password_confirmation' => 'short'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->change(['password_confirmation' => 'something-else'])
            ->assertStatus(422)->assertJsonPath('errors.password.0', __('portal.password.confirmed'));
        $this->change(['password' => 'old-secret', 'password_confirmation' => 'old-secret'])
            ->assertStatus(422)->assertJsonPath('errors.password.0', __('portal.password.different'));

        $this->assertTrue(Hash::check('old-secret', $this->teacher->fresh()->password));
    }

    public function test_guessing_the_current_password_is_rate_limited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->change(['current_password' => 'guess-' . $i])->assertStatus(422);
        }

        $this->change([])->assertStatus(429);
    }

    public function test_students_and_guests_cannot_use_it()
    {
        $this->putJson('/api/v1/teacher/password', [])->assertStatus(401);
        $this->actingAs(Student::factory()->create(), 'student')
            ->putJson('/api/v1/teacher/password', [])->assertStatus(403);
    }
}
