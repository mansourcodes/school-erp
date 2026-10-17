<?php

namespace Tests\Feature\Admin;

use App\Models\Course;
use App\Models\User;
use App\Support\ActiveCourses;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** Admin navbar (inc/main_header): active-term chips, brand and user. */
class AdminHeaderTest extends TestCase
{
    use RefreshDatabase;

    protected function admin()
    {
        $admin = User::factory()->create(['name' => 'منصور']);
        $admin->assignRole(Role::findOrCreate('Superadmin', 'web'));

        return $admin;
    }

    public function test_active_courses_are_grouped_by_term()
    {
        Course::factory()->create(['semester' => 'الأول', 'hijri_year' => '1447', 'is_active' => true]);
        Course::factory()->create(['semester' => 'الأول', 'hijri_year' => '1447', 'is_active' => true]);
        Course::factory()->create(['semester' => 'صيفي', 'hijri_year' => '1448', 'is_active' => true]);
        Course::factory()->create(['semester' => 'الثاني', 'hijri_year' => '1446', 'is_active' => false]);

        $terms = collect(ActiveCourses::terms())->keyBy('label');

        $this->assertSame(['الفصل صيفي 1448هـ', 'الفصل الأول 1447هـ'], $terms->keys()->all());
        $this->assertSame(2, $terms['الفصل الأول 1447هـ']['count']);
        $this->assertSame(1, $terms['الفصل صيفي 1448هـ']['count']);
    }

    public function test_the_navbar_shows_the_active_term_and_the_user()
    {
        Course::factory()->count(2)->create(['semester' => 'الأول', 'hijri_year' => '1447', 'is_active' => true]);
        Course::factory()->create(['semester' => 'الثاني', 'hijri_year' => '1446', 'is_active' => false]);

        $this->actingAs($this->admin(), 'backpack')->get(backpack_url('course'))->assertOk()
            ->assertSee('الفصل الأول 1447هـ')
            ->assertSee('دورتان')
            ->assertDontSee('الفصل الثاني 1446هـ')
            ->assertSee('class="header-term"', false)
            ->assertSee('منصور')
            ->assertSee('مدير النظام');
    }

    public function test_no_chip_without_an_active_course()
    {
        Course::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin(), 'backpack')->get(backpack_url('course'))->assertOk()
            ->assertDontSee('class="header-term"', false);
    }
}
