<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SettingCrudController;
use App\Models\User;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /admin/setting: shows each setting's key and filters the list by a key-derived category. */
class SettingCrudTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
    }

    protected function search($query = '')
    {
        return $this->actingAs($this->admin, 'backpack')
            ->postJson('/admin/setting/search' . $query, [], ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk();
    }

    public function test_keys_map_to_categories()
    {
        $this->assertSame('portal', SettingCrudController::categoryOf('portal.theme'));
        $this->assertSame('payments', SettingCrudController::categoryOf('payment_types'));
        $this->assertSame('reports', SettingCrudController::categoryOf('transcript.title'));
        $this->assertSame('general', SettingCrudController::categoryOf('organization_name'));
    }

    /** Backpack's CrudPanel is a singleton shared by a test's requests, so the page and the search are separate tests. */
    public function test_the_list_shows_the_key_column_and_category_filter()
    {
        $this->actingAs($this->admin, 'backpack')->get('/admin/setting')->assertOk()
            ->assertSee(trans('setting.key'))->assertSee(trans('setting.categories.reports'));
    }

    public function test_the_list_filters_by_category()
    {
        $rows = $this->search('?category=portal')->json('data');
        $keys = collect($rows)->map(fn ($row) => strip_tags($row[0]))->map(fn ($k) => trim($k));

        $this->assertNotEmpty($keys);
        $this->assertTrue($keys->every(fn ($k) => str_starts_with($k, 'portal.')), $keys->implode(', '));
    }

    public function test_the_edit_form_shows_the_key()
    {
        $setting = Setting::where('key', 'portal.theme')->firstOrFail();

        $this->actingAs($this->admin, 'backpack')->get("/admin/setting/{$setting->id}/edit")
            ->assertOk()->assertSee('portal.theme');
    }
}
