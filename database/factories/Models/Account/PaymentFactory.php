<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Account\Course;
use App\Models\Account\Payment;
use App\Models\Account\Student;

class PaymentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Payment::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'course_id' => Course::factory(),
            'student_id' => Student::factory(),
            'amount' => $this->faker->randomFloat(0, 0, 9999999999.),
            'source' => $this->faker->word,
            'meta' => $this->faker->text,
        ];
    }
}
