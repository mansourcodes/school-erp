<?php

namespace Tests\Feature;

use Tests\TestCase;

/** Backpack settings `portal.theme` and the panel's texts (PortalBranding). */
class PortalThemeTest extends TestCase
{
    public function test_it_serves_the_chosen_theme_without_signing_in()
    {
        config(['settings.portal.theme' => 'girls']);

        $this->getJson('/api/v1/theme')->assertOk()->assertJsonPath('data.theme', 'girls');
    }

    public function test_it_serves_the_portal_texts_from_the_settings()
    {
        config([
            'settings.portal.name' => 'بوابة النور',
            'settings.portal.short_name' => 'النور',
            'settings.portal.centre' => 'مركز النور',
            'settings.portal.description' => 'بوابة الطالب',
        ]);

        $this->getJson('/api/v1/theme')->assertOk()->assertJsonPath('data.name', 'بوابة النور')
            ->assertJsonPath('data.short_name', 'النور')
            ->assertJsonPath('data.centre', 'مركز النور')
            ->assertJsonPath('data.description', 'بوابة الطالب');
    }

    public function test_empty_texts_fall_back_to_the_portal_name()
    {
        config(['settings.portal.name' => 'بوابة النور', 'settings.portal.short_name' => ' ', 'settings.portal.centre' => null]);

        $this->getJson('/api/v1/theme')->assertJsonPath('data.short_name', 'بوابة النور')
            ->assertJsonPath('data.centre', 'بوابة النور');
    }

    public function test_the_app_manifest_uses_the_settings()
    {
        config(['settings.portal.name' => 'بوابة النور', 'settings.portal.short_name' => 'النور', 'settings.portal.description' => 'وصف']);

        $this->get('/beta/manifest.webmanifest')->assertOk()
            ->assertHeader('Content-Type', 'application/manifest+json')
            ->assertJsonPath('name', 'بوابة النور')
            ->assertJsonPath('short_name', 'النور')
            ->assertJsonPath('description', 'وصف')
            ->assertJsonPath('icons.3.purpose', 'maskable');
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
