<?php

namespace Tests\Feature\Api\V1;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/** The per-account login limit counts each teacher username on its own, so teachers sharing the school Wi-Fi are not throttled together. */
class TeacherLoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['portal.login.honeypot_delay_ms' => 0, 'services.turnstile.secret' => null]);
        Role::findOrCreate('Teacher', 'web');
        foreach (['ahmad', 'fatima'] as $username) {
            User::factory()->create(['username' => $username, 'password' => Hash::make('secret-pass')])->assignRole('Teacher');
        }

        $this->withHeaders(['Referer' => 'http://localhost/beta/login']);
    }

    protected function login($username, $password = 'secret-pass')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => '10.5.0.1'])
            ->postJson('/api/v1/auth/login', ['role' => 'teacher', 'username' => $username, 'password' => $password]);
    }

    public function test_one_username_is_limited_per_minute()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->login('ahmad', 'wrong')->assertStatus(422);
        }

        $this->login('ahmad')->assertStatus(429)->assertJsonPath('message', __('portal.auth.throttled'));
    }

    public function test_another_teacher_on_the_same_ip_is_not_throttled()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->login('ahmad', 'wrong')->assertStatus(422);
        }

        $this->login('fatima')->assertOk()->assertJsonPath('data.username', 'fatima');
    }

    public function test_the_username_is_counted_case_insensitively()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->login($i % 2 ? 'AHMAD' : ' ahmad ', 'wrong')->assertStatus(422);
        }

        $this->login('Ahmad')->assertStatus(429);
    }
}
