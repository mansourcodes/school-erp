<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Students change their own password from the portal profile after confirming the current one. */
class StudentPasswordTest extends TestCase
{
    use RefreshDatabase;

    protected $student;

    protected function setUp(): void
    {
        parent::setUp();
        // StudentObserver sets the initial password to the mobile number, so set ours after creation.
        $this->student = Student::factory()->create();
        $this->student->update(['password' => Hash::make('old-secret')]);
    }

    protected function change(array $body)
    {
        // From the SPA's origin, like the real app: Sanctum then starts the session the controller regenerates.
        return $this->actingAs($this->student, 'student')
            ->withHeaders(['Referer' => 'http://localhost/beta/profile'])
            ->putJson('/api/v1/student/password', $body + [
            'current_password' => 'old-secret',
            'password' => 'new-secret-1',
            'password_confirmation' => 'new-secret-1',
        ]);
    }

    public function test_a_student_changes_their_password_and_other_devices_sign_out()
    {
        $token = $this->student->fresh()->remember_token;
        $recaller = Auth::guard('student')->getRecallerName();

        $response = $this->change([])->assertNoContent();

        $fresh = $this->student->fresh();
        $this->assertTrue(Hash::check('new-secret-1', $fresh->password));
        // A new remember token signs the other devices out; this device gets a fresh remember cookie.
        $this->assertNotSame($token, $fresh->remember_token);
        $this->assertTrue(collect($response->headers->getCookies())->contains(function ($cookie) use ($recaller) {
            return $cookie->getName() === $recaller && ! $cookie->isCleared();
        }));
    }

    public function test_the_new_password_signs_in_on_the_portal()
    {
        $this->change([])->assertNoContent();
        Auth::guard('student')->logout();

        $this->withHeaders(['Referer' => 'http://localhost/beta/login'])
            ->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => $this->student->cpr, 'password' => 'new-secret-1'])
            ->assertOk();
    }

    public function test_a_wrong_current_password_is_rejected()
    {
        $this->change(['current_password' => 'guess'])
            ->assertStatus(422)
            ->assertJsonPath('errors.current_password.0', __('portal.password.current_wrong'));

        $this->assertTrue(Hash::check('old-secret', $this->student->fresh()->password));
    }

    public function test_the_new_password_is_validated()
    {
        $this->change(['password' => 'short', 'password_confirmation' => 'short'])
            ->assertStatus(422)->assertJsonValidationErrors('password');
        $this->change(['password_confirmation' => 'something-else'])
            ->assertStatus(422)->assertJsonPath('errors.password.0', __('portal.password.confirmed'));
        $this->change(['password' => 'old-secret', 'password_confirmation' => 'old-secret'])
            ->assertStatus(422)->assertJsonPath('errors.password.0', __('portal.password.different'));

        $this->assertTrue(Hash::check('old-secret', $this->student->fresh()->password));
    }

    public function test_guessing_the_current_password_is_rate_limited()
    {
        for ($i = 0; $i < 10; $i++) {
            $this->change(['current_password' => 'guess-' . $i])->assertStatus(422);
        }

        $this->change([])->assertStatus(429);
    }

    public function test_teachers_and_guests_cannot_use_it()
    {
        $this->putJson('/api/v1/student/password', [])->assertStatus(401);

        Role::findOrCreate('Teacher', 'web');
        $teacher = User::factory()->create();
        $teacher->assignRole('Teacher');
        $this->actingAs($teacher, 'web')->putJson('/api/v1/student/password', [])->assertStatus(403);
    }
}
