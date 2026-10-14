<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Backpack setting `portal.theme`: one colour theme for the whole new panel. */
class PortalThemeTest extends TestCase
{
    public function test_it_serves_the_chosen_theme_without_signing_in()
    {
        config(['settings.portal.theme' => 'girls']);

        $this->getJson('/api/v1/theme')->assertOk()->assertExactJson(['data' => ['theme' => 'girls']]);
    }

    public function test_an_unknown_or_missing_value_means_the_default_theme()
    {
        config(['settings.portal.theme' => null]);
        $this->getJson('/api/v1/theme')->assertJsonPath('data.theme', 'ghadeer');

        config(['settings.portal.theme' => 'dark']);
        $this->getJson('/api/v1/theme')->assertJsonPath('data.theme', 'ghadeer');
    }

    public function test_it_still_answers_while_the_portal_is_closed()
    {
        config(['settings.portal.mode' => 'closed', 'settings.portal.theme' => 'autumn']);

        $this->getJson('/api/v1/theme')->assertOk()->assertJsonPath('data.theme', 'autumn');
        $this->getJson('/api/v1/me')->assertStatus(403);
    }
}
