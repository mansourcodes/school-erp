<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Course;
use App\Models\ExamTool;

class ExamToolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExamTool::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'subject' => $this->faker->word,
            'course_id' => Course::factory(),
            'zip_file_path' => $this->faker->word,
            'zip_file_size' => $this->faker->word,
            'meta' => $this->faker->text,
        ];
    }
}
