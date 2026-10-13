<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PortalRateLimitTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_classroom_sharing_one_ip_is_not_throttled_together()
    {
        // 70 requests from one Wi-Fi IP, by 7 students: above the old 60/min per-IP limit.
        $students = Student::factory()->count(7)->create();

        foreach ($students as $student) {
            for ($i = 0; $i < 10; $i++) {
                $this->actingAs($student, 'student')
                    ->withServerVariables(['REMOTE_ADDR' => '10.9.0.1'])
                    ->getJson('/api/v1/me')
                    ->assertOk();
            }
        }
    }

    public function test_one_student_is_limited_per_minute()
    {
        $student = Student::factory()->create();

        for ($i = 0; $i < 120; $i++) {
            $this->actingAs($student, 'student')->getJson('/api/v1/me')->assertOk();
        }

        $this->actingAs($student, 'student')
            ->getJson('/api/v1/me')
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJsonPath('message', __('portal.errors.too_many_requests'));
    }
}
