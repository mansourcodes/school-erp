<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_login()
    {
        $this->getJson('/api/v1/student/profile')->assertStatus(401);
    }

    public function test_returns_only_whitelisted_fields()
    {
        $student = Student::factory()->create([
            'name' => 'حسين علي',
            'cpr' => '040512345',
            'dob' => '2004-05-18',
            'iqraa_id_account' => 'IQ-58214',
            'health_history_details' => 'سري',
            'financial_details' => 'سري',
            'student_notes' => 'سري',
        ]);

        $response = $this->actingAs($student, 'student')->getJson('/api/v1/student/profile')->assertOk();

        $response->assertJsonPath('data.name', 'حسين علي')
            // Leading zero restored (the column is an integer).
            ->assertJsonPath('data.cpr', '040512345')
            ->assertJsonPath('data.dob', '2004-05-18')
            ->assertJsonPath('data.iqraa_id_account', 'IQ-58214')
            ->assertJsonPath('data.student_id', $student->student_id);
        $this->assertSame(
            ['name', 'cpr', 'dob', 'age', 'degree', 'mobile', 'mobile2', 'email', 'address', 'student_id', 'registration_at', 'iqraa_id_account', 'photo_url'],
            array_keys($response->json('data'))
        );
        $this->assertStringNotContainsString('سري', $response->getContent());
    }
}
