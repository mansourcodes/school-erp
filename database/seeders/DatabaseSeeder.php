<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // \App\Models\User::factory(10)->create();


        \App\Models\CurriculumCategory::factory()->count(100)->create();
        $curricula = \App\Models\Curriculum::factory()->count(100)->create();
        $students = \App\Models\Student::factory()->count(100)->create();
        \App\Models\AcademicPath::factory()->count(100)->create();
        \App\Models\Course::factory()->count(100)->create();
        \App\Models\ClassRoom::factory()->count(100)->create();
        \App\Models\StudentMarks::factory()->count(100)->create();


        \App\Models\AcademicPath::All()->each(function ($academic_path) use ($curricula) {
            $academic_path->curricula()->attach(
                $curricula->random(rand(1, 6))->pluck('id')->toArray()
            );
        });

        \App\Models\ClassRoom::All()->each(function ($class_room) use ($students) {
            $class_room->students()->attach(
                $students->random(rand(1, 6))->pluck('id')->toArray()
            );
        });
    }
}
