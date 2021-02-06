<?php

namespace Database\Seeders;

use App\Models\StudentMarks;
use Illuminate\Database\Seeder;

class StudentMarksSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        StudentMarks::factory()->count(5)->create();
    }
}
