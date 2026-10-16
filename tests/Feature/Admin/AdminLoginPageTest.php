<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;

/** Backpack's login page behind `guest:backpack` (RedirectIfAuthenticated). */
class AdminLoginPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_guest_sees_the_login_page()
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_a_signed_in_admin_goes_to_the_dashboard()
    {
        $admin = User::factory()->create();
        $admin->assignRole(Role::findOrCreate('Superadmin', 'web'));

        $this->actingAs($admin, 'backpack')->get('/admin/login')->assertRedirect(backpack_url('dashboard'));
    }

    /** Production installs with --no-dev: a test-only import in app code is a 500 there (it was one, on the login page). */
    public function test_app_code_imports_nothing_from_dev_only_packages()
    {
        $offenders = [];
        foreach (Finder::create()->files()->name('*.php')->in([app_path(), config_path(), base_path('routes'), database_path('migrations')]) as $file) {
            if (preg_match('/^use (function )?(PHPUnit|Mockery|Faker|Tests)\\\\/m', $file->getContents())) {
                $offenders[] = $file->getRelativePathname();
            }
        }

        $this->assertSame([], $offenders);
    }
}
