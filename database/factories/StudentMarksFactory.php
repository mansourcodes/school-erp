<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\StudentMarks;

class StudentMarksFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = StudentMarks::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'student_id' => $this->faker->numberBetween(1, 100),
            'course_id' => $this->faker->numberBetween(1, 100),
            'marks' => $this->faker->text,
        ];
    }
}
