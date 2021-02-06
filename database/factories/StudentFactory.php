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
            'student_name' => $this->faker->word,
            'cpr' => $this->faker->randomNumber(),
            'email' => $this->faker->safeEmail,
            'mobile' => $this->faker->randomNumber(),
            'mobile2' => $this->faker->randomNumber(),
            'dob' => $this->faker->date(),
            'address' => $this->faker->word,
            'live_inـstate' => $this->faker->randomElement(["UNKNOWN","OWN","RENT"]),
            'relationshipـstate' => $this->faker->randomElement(["UNKNOWN","SINGLE","MARRIED","DIVORCED"]),
            'family_members' => $this->faker->randomNumber(),
            'family_depends' => $this->faker->randomNumber(),
            'degree' => $this->faker->word,
            'hawzaـhistory' => $this->faker->boolean,
            'hawzaـhistory_details' => $this->faker->text,
            'healthـhistory' => $this->faker->boolean,
            'healthـhistory_details' => $this->faker->text,
            'financialـstate' => $this->faker->randomElement(["UNKNOWN","POOR","AVERAGE","GOOD","EXCELLENT"]),
            'financial_details' => $this->faker->text,
            'student_notes' => $this->faker->text,
            'registration_at' => $this->faker->date(),
        ];
    }
}
