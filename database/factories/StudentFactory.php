<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Student;

class StudentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Student::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {


        return [
            'student_name' => $this->faker->name('male'),
            'cpr' => $this->faker->numerify('##########'),
            'email' => $this->faker->safeEmail,
            'mobile' => $this->faker->numerify('973########'),
            'mobile2' => $this->faker->numerify('973########'),
            'dob' => $this->faker->date(),
            'address' => $this->faker->address,
            'live_in_state' => $this->faker->randomElement(["UNKNOWN", "OWN", "RENT"]),
            'relationship_state' => $this->faker->randomElement(["UNKNOWN", "SINGLE", "MARRIED", "DIVORCED"]),
            'family_members' => $this->faker->randomNumber(),
            'family_depends' => $this->faker->randomNumber(),
            'degree' => $this->faker->randomElement(["جامعي", "ثانوي", "ماستر", "حوزوي"]),
            'hawza_history' => $this->faker->boolean,
            'hawza_history_details' => $this->faker->text,
            'health_history' => $this->faker->boolean,
            'health_history_details' => $this->faker->paragraph(rand(1, 3)),
            'financial_state' => $this->faker->randomElement(["UNKNOWN", "POOR", "AVERAGE", "GOOD", "EXCELLENT"]),
            'financial_details' => $this->faker->paragraph(rand(1, 3)),
            'student_notes' => $this->faker->paragraph(rand(1, 3)),
            'registration_at' => $this->faker->date(),
        ];
    }
}
