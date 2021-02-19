<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AcademicPath;

class AcademicPathFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AcademicPath::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'academic_path_name' => $this->faker->company,
            'academic_path_type' => $this->faker->randomElement(["منتظم", "عن بعد", "دورة", "متقدم"]),

        ];
    }
}
