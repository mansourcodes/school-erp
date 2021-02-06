<?php

namespace Database\Seeders;

use App\Models\AcademicPath;
use Illuminate\Database\Seeder;

class AcademicPathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        AcademicPath::factory()->count(5)->create();
    }
}
