<?php

namespace Database\Seeders;

use App\Models\CurriculumCategory;
use Illuminate\Database\Seeder;

class CurriculumCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        CurriculumCategory::factory()->count(5)->create();
    }
}
