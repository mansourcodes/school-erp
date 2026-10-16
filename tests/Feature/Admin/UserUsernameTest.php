<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** The admin panel signs users in with their username; the users CRUD sets it. */
class UserUsernameTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['username' => 'admin', 'email' => 'admin@example.com', 'password' => Hash::make('secret-pass')]);
        $this->admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
    }

    public function test_the_admin_panel_signs_in_with_the_username()
    {
        $this->post('/admin/login', ['username' => 'admin', 'password' => 'secret-pass'])->assertRedirect();

        $this->assertAuthenticatedAs($this->admin, 'backpack');
    }

    public function test_the_email_no_longer_signs_in_to_the_admin_panel()
    {
        $this->post('/admin/login', ['username' => 'admin@example.com', 'password' => 'secret-pass'])
            ->assertSessionHasErrors('username');

        $this->assertGuest('backpack');
    }

    public function test_the_admin_creates_a_user_with_a_username()
    {
        $this->actingAs($this->admin, 'backpack')->post('/admin/user', [
            'name' => 'Teacher',
            'username' => 'New.Teacher',
            'email' => 'new@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ])->assertSessionHasNoErrors()->assertRedirect();

        $this->assertDatabaseHas('users', ['email' => 'new@example.com', 'username' => 'new.teacher']);
    }

    public function test_the_username_is_required_unique_and_well_formed()
    {
        $base = ['name' => 'Teacher', 'email' => 'new@example.com', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass'];

        foreach (['', 'Admin', 'two words', 'أحمد'] as $username) {
            $this->actingAs($this->admin, 'backpack')->post('/admin/user', $base + ['username' => $username])
                ->assertSessionHasErrors('username');
        }

        $this->assertDatabaseMissing('users', ['email' => 'new@example.com']);
    }

    public function test_the_email_is_optional_but_unique_when_set()
    {
        $form = ['name' => 'Teacher', 'password' => 'secret-pass', 'password_confirmation' => 'secret-pass'];

        $this->actingAs($this->admin, 'backpack')->post('/admin/user', $form + ['username' => 'no.email', 'email' => ''])
            ->assertSessionHasNoErrors();
        $this->actingAs($this->admin, 'backpack')->post('/admin/user', $form + ['username' => 'no.email2'])
            ->assertSessionHasNoErrors();
        $this->assertSame(2, User::whereNull('email')->count());

        $this->actingAs($this->admin, 'backpack')->post('/admin/user', $form + ['username' => 'dup', 'email' => 'admin@example.com'])
            ->assertSessionHasErrors('email');
        $this->actingAs($this->admin, 'backpack')->post('/admin/user', $form + ['username' => 'bad', 'email' => 'not-an-email'])
            ->assertSessionHasErrors('email');
    }

    public function test_several_users_can_have_their_email_cleared()
    {
        $users = User::factory()->count(2)->create();

        foreach ($users as $user) {
            $this->actingAs($this->admin, 'backpack')->put("/admin/user/{$user->id}", [
                'id' => $user->id, 'name' => $user->name, 'username' => $user->username, 'email' => '',
            ])->assertSessionHasNoErrors();
        }

        $this->assertSame(2, User::whereNull('email')->count());
    }

    public function test_updating_a_user_keeps_their_own_username_valid()
    {
        $teacher = User::factory()->create(['username' => 'teacher']);
        $form = ['id' => $teacher->id, 'name' => $teacher->name, 'username' => 'teacher', 'email' => $teacher->email];

        $this->actingAs($this->admin, 'backpack')->put("/admin/user/{$teacher->id}", $form)->assertSessionHasNoErrors();
        $this->actingAs($this->admin, 'backpack')->put("/admin/user/{$teacher->id}", ['username' => 'teacher2'] + $form)->assertSessionHasNoErrors();

        $this->assertSame('teacher2', $teacher->fresh()->username);
    }

    public function test_editing_a_user_without_touching_the_photo_keeps_it()
    {
        Storage::fake('public');
        Storage::disk('public')->put('teachers/photos/1/old.webp', 'old');
        $teacher = User::factory()->create(['username' => 'teacher']);
        $teacher->forceFill(['photo_path' => 'teachers/photos/1/old.webp'])->save();
        $form = ['id' => $teacher->id, 'name' => 'New Name', 'username' => 'teacher', 'email' => $teacher->email];

        // A browser posts the untouched file input as an empty part (UPLOAD_ERR_NO_FILE).
        $this->actingAs($this->admin, 'backpack')
            ->call('PUT', "/admin/user/{$teacher->id}", $form, [], ['photo_path' => SettingCrudTest::emptyUpload()])
            ->assertSessionHasNoErrors();

        $this->assertSame('New Name', $teacher->fresh()->name);
        $this->assertSame('teachers/photos/1/old.webp', $teacher->fresh()->photo_path);
        Storage::disk('public')->assertExists('teachers/photos/1/old.webp');
    }

    public function test_clearing_the_photo_deletes_it()
    {
        Storage::fake('public');
        Storage::disk('public')->put('teachers/photos/1/old.webp', 'old');
        $teacher = User::factory()->create(['username' => 'teacher']);
        $teacher->forceFill(['photo_path' => 'teachers/photos/1/old.webp'])->save();
        $form = ['id' => $teacher->id, 'name' => $teacher->name, 'username' => 'teacher', 'email' => $teacher->email];

        $this->actingAs($this->admin, 'backpack')->put("/admin/user/{$teacher->id}", ['photo_path' => ''] + $form)->assertSessionHasNoErrors();

        $this->assertNull($teacher->fresh()->photo_path);
        Storage::disk('public')->assertMissing('teachers/photos/1/old.webp');
    }
}
