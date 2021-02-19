<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Course;

class CourseFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Course::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $year = $this->faker->numberBetween(2000, 2020);
        return [
            'course_year' => $year . '/' . ($year + 1),
            'hijri_year' => $this->faker->numberBetween(1440, 1443),
            'semester' => $this->faker->randomElement(["الاول", "صيفي", "الثاني", "مسائي رجال"]),
            'duration' => $this->faker->numberBetween(3, 3) . ' شهور',
            'academic_path_id' => $this->faker->numberBetween(1, 10),
        ];
    }
}
