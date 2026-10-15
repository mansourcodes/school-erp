<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Services\Portal\TeacherImpersonation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** A Superadmin opens the teacher portal as a teacher from Backpack → Users. */
class TeacherImpersonationTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        Role::findOrCreate('Superadmin', 'web');
        Role::findOrCreate('Teacher', 'web');
        $this->admin = User::factory()->create(['name' => 'Head Admin']);
        $this->admin->assignRole('Superadmin');
        $this->teacher = User::factory()->create(['name' => 'Ali Hasan', 'password' => Hash::make('secret-pass')]);
        $this->teacher->assignRole('Teacher');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    protected function start($admin = null, $teacher = null)
    {
        return $this->actingAs($admin ?: $this->admin, 'backpack')
            ->post('/admin/user/' . ($teacher ?: $this->teacher)->id . '/portal');
    }

    protected function api()
    {
        return $this->withHeaders(['Referer' => 'http://localhost/beta/teacher']);
    }

    public function test_a_superadmin_opens_the_portal_as_the_teacher()
    {
        $this->start()->assertRedirect('/beta/teacher');

        $this->assertSame($this->teacher->id, Auth::guard('web')->id());
        $this->api()->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.id', $this->teacher->id)
            ->assertJsonPath('data.impersonated_by.name', 'Head Admin');
        $this->api()->getJson('/api/v1/teacher/classrooms')->assertOk();
    }

    public function test_no_remember_cookie_is_set_and_the_teacher_keeps_their_token()
    {
        $token = $this->teacher->remember_token;
        $recaller = Auth::guard('web')->getRecallerName();

        $response = $this->start();

        $this->assertFalse(collect($response->headers->getCookies())->contains(function ($cookie) use ($recaller) {
            return $cookie->getName() === $recaller && ! $cookie->isCleared();
        }));
        $this->assertSame($token, $this->teacher->fresh()->remember_token);
    }

    public function test_a_teacher_in_backpack_cannot_impersonate()
    {
        $other = User::factory()->create();
        $other->assignRole('Teacher');

        // RedirectTeacherToAttendance turns teachers away first; the controller also refuses non-Superadmins.
        $this->start($other)->assertRedirect();
        $this->assertNull(Auth::guard('web')->id());
    }

    public function test_only_teachers_can_be_opened()
    {
        $plain = User::factory()->create();

        $this->start(null, $plain)->assertNotFound();
        $this->post('/admin/user/999999/portal')->assertNotFound();
    }

    public function test_the_teachers_own_account_endpoints_are_blocked()
    {
        $this->start();

        $this->api()->putJson('/api/v1/teacher/password', [
            'current_password' => 'secret-pass', 'password' => 'new-secret-1', 'password_confirmation' => 'new-secret-1',
        ])->assertForbidden();
        $this->api()->putJson('/api/v1/teacher/username', ['username' => 'someone.else'])->assertForbidden();
        $this->api()->deleteJson('/api/v1/teacher/photo')->assertForbidden();

        $this->assertTrue(Hash::check('secret-pass', $this->teacher->fresh()->password));
    }

    public function test_it_expires_after_the_ttl()
    {
        config(['portal.impersonation.ttl_minutes' => 60]);
        $this->start();

        Carbon::setTestNow(now()->addMinutes(61));

        $this->api()->getJson('/api/v1/me')->assertUnauthorized();
        $this->assertNull(Auth::guard('web')->id());
        $this->assertFalse(session()->has(TeacherImpersonation::IMPERSONATOR));
    }

    public function test_it_ends_when_the_admin_loses_the_superadmin_role()
    {
        $this->start();
        $this->admin->removeRole('Superadmin');

        $this->api()->getJson('/api/v1/me')->assertUnauthorized();
    }

    public function test_logout_ends_it_but_keeps_the_backpack_session()
    {
        $this->start();

        $this->api()->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertNull(Auth::guard('web')->id());
        $this->assertSame($this->admin->id, Auth::guard('backpack')->id());
        $this->assertFalse(session()->has(TeacherImpersonation::IMPERSONATOR));
    }

    public function test_a_real_teacher_has_no_impersonator()
    {
        $this->actingAs($this->teacher, 'web');

        $this->api()->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.impersonated_by', null);
    }
}
