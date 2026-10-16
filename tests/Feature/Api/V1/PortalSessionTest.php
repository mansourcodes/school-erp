<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** A new password ends the user's other live portal sessions (portal.session), not only their remember cookies. */
class PortalSessionTest extends TestCase
{
    use RefreshDatabase;

    protected $student;

    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();

        config(['portal.login.honeypot_delay_ms' => 0, 'services.turnstile.secret' => null]);
        // StudentObserver sets the initial password to the mobile number, so set ours after creation.
        $this->student = Student::factory()->create(['cpr' => '901234567']);
        $this->student->update(['password' => Hash::make('secret-pass')]);
        Role::findOrCreate('Teacher', 'web');
        $this->teacher = User::factory()->create(['username' => 'ahmad', 'password' => Hash::make('secret-pass')]);
        $this->teacher->assignRole('Teacher');

        $this->withHeaders(['Referer' => 'http://localhost/beta/login']);
    }

    /** The next request, as from the browser: the guard loads the user from the session again. */
    protected function nextRequest()
    {
        // Laravel 8.29 has no Auth::forgetGuards(): drop the manager, the next request resolves a fresh one.
        $this->app->forgetInstance('auth');
        $this->app->forgetInstance('auth.driver');
        Auth::clearResolvedInstances();

        return $this;
    }

    public function test_a_password_reset_elsewhere_signs_the_student_session_out()
    {
        $this->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => '901234567', 'password' => 'secret-pass'])->assertOk();
        $this->nextRequest()->getJson('/api/v1/me')->assertOk();

        // e.g. the administration resets it after an ID check.
        $this->student->update(['password' => Hash::make('reset-by-admin')]);

        $this->nextRequest()->getJson('/api/v1/me')->assertStatus(401);
        $this->assertGuest('student');
        $this->nextRequest()->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_a_password_reset_elsewhere_signs_the_teacher_session_out()
    {
        $this->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => 'ahmad', 'password' => 'secret-pass'])->assertOk();
        $this->nextRequest()->getJson('/api/v1/me')->assertOk();

        $this->teacher->update(['password' => Hash::make('reset-by-admin')]);

        $this->nextRequest()->getJson('/api/v1/me')->assertStatus(401);
        $this->assertGuest('web');
    }

    public function test_changing_the_own_password_keeps_this_device_signed_in()
    {
        $this->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => '901234567', 'password' => 'secret-pass'])->assertOk();

        $this->nextRequest()->putJson('/api/v1/student/password', [
            'current_password' => 'secret-pass',
            'password' => 'new-secret-1',
            'password_confirmation' => 'new-secret-1',
        ])->assertNoContent();

        $this->nextRequest()->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $this->student->id);
    }

    public function test_an_unchanged_password_keeps_the_session()
    {
        $this->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => '901234567', 'password' => 'secret-pass'])->assertOk();
        $this->student->update(['name' => 'اسم جديد']);

        $this->nextRequest()->getJson('/api/v1/me')->assertOk();
    }

    public function test_a_teacher_signing_in_after_a_student_on_the_same_phone_is_the_teacher()
    {
        $this->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => '901234567', 'password' => 'secret-pass'])->assertOk();
        $token = $this->student->fresh()->remember_token;

        $this->nextRequest()->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => 'ahmad', 'password' => 'secret-pass'])->assertOk();

        $this->nextRequest()->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.role', 'teacher');
        $this->assertGuest('student');
        // The student's other devices stay signed in.
        $this->assertSame($token, $this->student->fresh()->remember_token);
    }

    public function test_a_student_signing_in_after_a_teacher_on_the_same_phone_is_the_student()
    {
        $this->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => 'ahmad', 'password' => 'secret-pass'])->assertOk();

        $this->nextRequest()->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => '901234567', 'password' => 'secret-pass'])->assertOk();

        $this->nextRequest()->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.role', 'student');
        $this->assertGuest('web');
    }

    public function test_an_impersonation_ends_but_the_admin_stays_in_backpack()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
        // A session login (not actingAs), so the Backpack sign-in survives nextRequest() like a real one.
        Auth::guard('backpack')->login($admin);
        $this->post("/admin/user/{$this->teacher->id}/portal")->assertRedirect();
        $this->nextRequest()->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.id', $this->teacher->id);

        $this->teacher->update(['password' => Hash::make('reset-by-admin')]);

        $this->nextRequest()->getJson('/api/v1/me')->assertStatus(401);
        $this->assertGuest('web');
        $this->assertAuthenticatedAs($admin, 'backpack');
    }
}
