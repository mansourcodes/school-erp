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
            'curriculumـname' => $this->faker->word,
            'bookـname' => $this->faker->word,
            'weightـinـhours' => $this->faker->randomNumber(),
        ];
    }
}
