<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Curriculum;

class CurriculumFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Curriculum::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'curriculum_name' => $this->faker->city,
            'book_name' => $this->faker->address,
            'weight_in_hours' => $this->faker->numberBetween(2, 6),
            'curriculum_category_id' => $this->faker->numberBetween(1, 10)
        ];
    }
}
