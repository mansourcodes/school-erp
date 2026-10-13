<?php

namespace Tests\Feature;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/** PLAN.md §4 "Portal mode switch": beta / live / maintenance / closed; /admin never affected. */
class PortalModeTest extends TestCase
{
    use RefreshDatabase;

    protected function mode($mode)
    {
        config(['settings.portal.mode' => $mode]);
    }

    public function test_beta_keeps_the_old_portal_and_invites_to_the_new_one()
    {
        $this->mode('beta');

        $this->get('/login')->assertOk()->assertSee(__('localize.portal_beta_banner'))->assertSee(url('/beta'));
        $this->getJson('/api/v1/me')->assertStatus(401);
    }

    public function test_an_unknown_or_missing_value_means_beta()
    {
        $this->mode(null);
        $this->get('/login')->assertOk()->assertSee(__('localize.portal_beta_banner'));

        $this->mode('nonsense');
        $this->get('/login')->assertOk();
    }

    public function test_live_sends_the_old_portal_to_the_new_panel()
    {
        $this->mode('live');

        $this->get('/login')->assertRedirect('/beta/login');
        $this->get('/dashboard')->assertRedirect('/beta');
        $this->get('/')->assertRedirect('/beta');
        $this->get('/login')->assertDontSee(__('localize.portal_beta_banner'));
    }

    public function test_maintenance_shows_a_notice_and_the_api_answers_503()
    {
        $this->mode('maintenance');

        $this->get('/login')->assertStatus(503)->assertSee(__('localize.portal_maintenance_title'));
        $this->postJson('/api/v1/auth/login', ['role' => 'student', 'cpr' => '901234567', 'password' => 'x'])
            ->assertStatus(503)
            ->assertHeader('Retry-After', 300)
            ->assertJsonPath('code', 'portal_maintenance');
    }

    public function test_closed_blocks_both_portals_even_when_signed_in()
    {
        $this->mode('closed');
        $student = Student::factory()->create();

        $this->actingAs($student, 'student')->get('/dashboard')->assertStatus(403)->assertSee(__('localize.portal_closed_title'));
        $this->actingAs($student, 'student')->getJson('/api/v1/me')->assertStatus(403)->assertJsonPath('code', 'portal_closed');
    }

    public function test_admin_is_never_affected()
    {
        foreach (['live', 'maintenance', 'closed'] as $mode) {
            $this->mode($mode);
            $this->get('/admin/login')->assertOk();
        }
    }

    public function test_the_old_login_form_is_rate_limited_too()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->from('/login')->post('/login', ['cpr' => '901234567', 'password' => 'wrong']);
        }

        $this->from('/login')->post('/login', ['cpr' => '901234567', 'password' => 'wrong'])
            ->assertRedirect('/login')
            ->assertSessionHasErrors(['cpr' => __('localize.too_many_login_attempts')]);
    }

    public function test_beta_paths_serve_the_app_shell()
    {
        if (! is_file(public_path('beta/index.html'))) {
            $this->markTestSkipped('SPA not built (cd frontend && npm run build).');
        }

        $response = $this->get('/beta/attendance/stats');

        // index.html must revalidate, so a new build is picked up at once.
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $response->assertOk();
        // A file response streams from disk; check which file it is.
        $this->assertSame(realpath(public_path('beta/index.html')), $response->baseResponse->getFile()->getRealPath());
    }
}
