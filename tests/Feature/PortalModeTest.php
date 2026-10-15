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

    protected function requireBuild()
    {
        if (! is_file(public_path('beta/index.html'))) {
            $this->markTestSkipped('SPA not built (cd frontend && npm run build).');
        }
    }

    public function test_live_serves_the_new_panel_at_the_root()
    {
        $this->requireBuild();
        $this->mode('live');

        // The old portal's pages are the new panel's own pages now, without /beta.
        $this->get('/')->assertOk()->assertSee('<div id="root"></div>', false);
        $this->get('/login')->assertOk()->assertSee('<div id="root"></div>', false)
            ->assertDontSee(__('localize.portal_beta_banner'));
        $this->get('/attendance/stats')->assertOk()->assertSee('<div id="root"></div>', false);
        $this->get('/teacher/classes/5')->assertOk();
        $this->get('/dashboard')->assertRedirect('/');
    }

    public function test_live_redirects_beta_bookmarks_to_the_root()
    {
        $this->mode('live');

        $this->get('/beta')->assertRedirect('/');
        $this->get('/beta/')->assertRedirect('/');
        $this->get('/beta/teacher/classes/5?tab=marks')->assertRedirect('/teacher/classes/5?tab=marks');
        // Assets are static files in public/beta, served before Laravel; the manifest stays there too.
        $this->get('/beta/manifest.webmanifest')->assertOk()
            ->assertJsonPath('start_url', '/')->assertJsonPath('scope', '/')
            ->assertJsonPath('shortcuts.1.url', '/attendance');
    }

    public function test_live_serves_the_worker_at_the_root_scope()
    {
        $this->requireBuild();

        $this->mode('beta');
        $this->get('/sw.js')->assertNotFound();

        $this->mode('live');
        $response = $this->get('/sw.js')->assertOk();
        $this->assertStringContainsString('javascript', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
    }

    public function test_root_paths_are_the_panel_only_when_live()
    {
        $this->mode('beta');
        $this->get('/attendance')->assertNotFound();
        $this->get('/beta/manifest.webmanifest')->assertJsonPath('start_url', '/beta/');

        // Server areas never become panel pages.
        $this->mode('live');
        $this->get('/admin/no-such-page')->assertNotFound();
        $this->get('/api/v1/no-such-endpoint')->assertNotFound();
        $this->post('/attendance')->assertStatus(405);
    }

    public function test_the_api_tells_the_app_its_page_base()
    {
        $this->mode('beta');
        $this->getJson('/api/v1/theme')->assertJsonPath('data.base', '/beta/');

        $this->mode('live');
        $this->getJson('/api/v1/theme')->assertJsonPath('data.base', '/');
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
        $this->requireBuild();

        config(['settings.portal.name' => 'بوابة <النور>', 'settings.portal.short_name' => 'النور']);

        $response = $this->get('/beta/attendance/stats');

        // index.html must revalidate, so a new build (or a changed setting) is picked up at once.
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control'));
        $response->assertOk();
        $response->assertSee('<div id="root"></div>', false);
        // The texts come from Backpack Settings, escaped.
        $response->assertSee('<title>بوابة &lt;النور&gt;</title>', false);
        $response->assertSee('<meta name="apple-mobile-web-app-title" content="النور" />', false);
    }
}
