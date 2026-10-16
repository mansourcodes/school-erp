<?php

namespace Tests\Feature\Admin;

use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** The CRUD forms' select2 lookups are behind the Backpack login: they list CPRs (the student sign-in) and phones. */
class AdminLookupTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->admin->assignRole(Role::findOrCreate('Superadmin', 'web'));
    }

    public function lookups()
    {
        return [['student'], ['course'], ['classroom'], ['teacher']];
    }

    /** @dataProvider lookups */
    public function test_the_old_public_urls_are_gone($lookup)
    {
        $this->getJson("/api/{$lookup}")->assertNotFound();
    }

    /** @dataProvider lookups */
    public function test_guests_cannot_use_them($lookup)
    {
        $this->get("/admin/api/{$lookup}")->assertRedirect();
        $this->assertGuest('backpack');
    }

    public function test_a_teacher_in_backpack_cannot_list_students()
    {
        $teacher = User::factory()->create();
        $teacher->assignRole(Role::findOrCreate('Teacher', 'web'));

        $this->actingAs($teacher, 'backpack')->get('/admin/api/student')->assertRedirect();
    }

    public function test_the_admin_searches_students_and_gets_only_the_label()
    {
        $student = Student::factory()->create(['name' => 'محمد علي', 'cpr' => '901234567', 'address' => 'Manama Road 1']);

        $row = $this->actingAs($this->admin, 'backpack')
            ->getJson('/admin/api/student?q=901234567')
            ->assertOk()
            ->json('data.0');

        $this->assertSame(['id', 'long_name'], array_keys($row));
        $this->assertSame($student->id, $row['id']);
        $this->assertStringContainsString('901234567', $row['long_name']);
    }
}
