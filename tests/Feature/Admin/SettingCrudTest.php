<?php

namespace Tests\Feature\Admin;

use App\Http\Controllers\Admin\SettingCrudController;
use App\Models\User;
use Backpack\Settings\app\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** /admin/setting: shows each setting's key, filters the list by a key-derived category and stores uploaded logos. */
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

    protected function updateLogo(array $input)
    {
        $setting = Setting::where('key', 'logo')->firstOrFail();

        return $this->actingAs($this->admin, 'backpack')
            ->put("/admin/setting/{$setting->id}", $input + ['id' => $setting->id]);
    }

    public function test_an_uploaded_logo_is_stored_and_replaces_the_previous_one()
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/old.png', 'old');
        Setting::where('key', 'logo')->update(['value' => 'settings/old.png']);

        $this->updateLogo(['value' => UploadedFile::fake()->image('logo.png', 200, 80)])->assertRedirect();

        $path = Setting::get('logo');
        $this->assertStringStartsWith('settings/logo-', $path);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertMissing('settings/old.png');
    }

    public function test_clearing_the_logo_deletes_the_file()
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/old.png', 'old');
        Setting::where('key', 'logo')->update(['value' => 'settings/old.png']);

        $this->updateLogo(['value' => ''])->assertRedirect();

        $this->assertSame('', (string) Setting::get('logo'));
        Storage::disk('public')->assertMissing('settings/old.png');
    }

    public function test_a_logo_must_be_a_raster_image()
    {
        Storage::fake('public');

        $this->updateLogo(['value' => UploadedFile::fake()->create('logo.svg', 10, 'image/svg+xml')])
            ->assertSessionHasErrors('value');
    }

    public function test_the_logo_is_shown_on_the_login_page_and_navbar()
    {
        config(['settings.logo' => 'settings/logo.png']);
        $url = Storage::disk('public')->url('settings/logo.png');

        $this->get('/admin/login')->assertOk()->assertSee($url, false);
        $this->actingAs($this->admin, 'backpack')->get('/admin/setting')->assertOk()->assertSee($url, false);
    }
}
