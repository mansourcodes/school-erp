<?php

namespace Tests\Feature\Admin;

use App\Support\AdminTheme;
use Tests\TestCase;

/** Backpack setting `admin.theme`: colour theme of the admin panel. */
class AdminThemeTest extends TestCase
{
    public function test_the_admin_pages_carry_the_chosen_theme()
    {
        config(['settings.admin.theme' => 'dark-blue']);

        $this->get(backpack_url('login'))->assertOk()
            ->assertSee('data-admin-theme="dark-blue"', false)
            ->assertSee('css/admin-themes.css', false);
    }

    public function test_an_unknown_or_missing_value_means_the_default_theme()
    {
        config(['settings.admin.theme' => null]);
        $this->assertSame(AdminTheme::DEFAULT, AdminTheme::current());

        config(['settings.admin.theme' => 'pink']);
        $this->assertSame(AdminTheme::DEFAULT, AdminTheme::current());
    }

    public function test_every_theme_has_its_colours_in_the_stylesheet()
    {
        $css = file_get_contents(public_path('css/admin-themes.css'));

        foreach (array_keys(AdminTheme::ALL) as $theme) {
            if ($theme !== AdminTheme::DEFAULT) {
                $this->assertStringContainsString('html[data-admin-theme="'.$theme.'"] {', $css);
            }
        }
    }
}
