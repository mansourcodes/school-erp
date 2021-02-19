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
            'live_inـstate' => $this->faker->randomElement(["UNKNOWN", "OWN", "RENT"]),
            'relationshipـstate' => $this->faker->randomElement(["UNKNOWN", "SINGLE", "MARRIED", "DIVORCED"]),
            'family_members' => $this->faker->randomNumber(),
            'family_depends' => $this->faker->randomNumber(),
            'degree' => $this->faker->randomElement(["جامعي", "ثانوي", "ماستر", "حوزوي"]),
            'hawzaـhistory' => $this->faker->boolean,
            'hawzaـhistory_details' => $this->faker->text,
            'healthـhistory' => $this->faker->boolean,
            'healthـhistory_details' => $this->faker->paragraph(rand(1, 3)),
            'financialـstate' => $this->faker->randomElement(["UNKNOWN", "POOR", "AVERAGE", "GOOD", "EXCELLENT"]),
            'financial_details' => $this->faker->paragraph(rand(1, 3)),
            'student_notes' => $this->faker->paragraph(rand(1, 3)),
            'registration_at' => $this->faker->date(),
        ];
    }
}
