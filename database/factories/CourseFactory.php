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
        return [
            'course_year' => $this->faker->word,
            'hijri_year' => $this->faker->word,
            'semester' => $this->faker->word,
            'duration' => $this->faker->word,
        ];
    }
}
