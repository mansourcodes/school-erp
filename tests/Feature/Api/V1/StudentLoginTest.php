<?php

namespace Tests\Feature\Api\V1;

use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class StudentLoginTest extends TestCase
{
    use RefreshDatabase;

    protected $student;

    protected function setUp(): void
    {
        parent::setUp();

        config(['portal.login.honeypot_delay_ms' => 0, 'services.turnstile.secret' => null]);

        // StudentObserver sets the initial password to the mobile number, so set ours after creation.
        $this->student = Student::factory()->create(['name' => 'محمد علي حسن', 'cpr' => '901234567']);
        $this->student->update(['password' => Hash::make('secret-pass')]);

        // Requests from a stateful domain get the Sanctum cookie session.
        $this->withHeaders(['Referer' => 'http://localhost/beta/login']);
    }

    protected function login(array $overrides = [], $ip = '10.0.0.1')
    {
        return $this->withServerVariables(['REMOTE_ADDR' => $ip])->postJson('/api/v1/auth/login', array_merge([
            'role' => 'student',
            'cpr' => '901234567',
            'password' => 'secret-pass',
        ], $overrides));
    }

    public function test_student_logs_in_and_reads_me()
    {
        $this->login()
            ->assertOk()
            ->assertJsonPath('data.role', 'student')
            ->assertJsonPath('data.id', $this->student->id)
            ->assertJsonPath('data.first_name', 'محمد')
            ->assertJsonMissing(['cpr' => 901234567]);

        $this->assertAuthenticatedAs($this->student, 'student');

        $this->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.student_id', $this->student->student_id)
            ->assertJsonStructure(['data' => ['role', 'id', 'name', 'first_name', 'student_id', 'photo_url']]);
    }

    public function test_me_requires_login()
    {
        $this->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertExactJson(['message' => __('portal.errors.unauthenticated')]);
    }

    public function test_logout_ends_the_session()
    {
        $this->login()->assertOk();

        $this->postJson('/api/v1/auth/logout')->assertNoContent();

        $this->assertGuest('student');
    }

    public function test_wrong_password_and_unknown_cpr_get_the_same_answer()
    {
        $wrongPassword = $this->login(['password' => 'nope'], '10.0.0.2');
        $unknownCpr = $this->login(['cpr' => '900000001', 'password' => 'nope'], '10.0.0.3');

        $wrongPassword->assertStatus(422);
        $unknownCpr->assertStatus(422);
        $this->assertSame($wrongPassword->json(), $unknownCpr->json());
        $this->assertSame(__('portal.auth.failed'), $wrongPassword->json('message'));
        $this->assertGuest('student');
    }

    public function test_input_is_validated_before_the_database()
    {
        $this->login(['cpr' => '12ab'])->assertStatus(422)->assertJsonValidationErrors('cpr');
        $this->login(['password' => str_repeat('x', 129)])->assertStatus(422)->assertJsonValidationErrors('password');
        $this->login(['role' => 'admin'])->assertStatus(422)->assertJsonValidationErrors('role');
    }

    public function test_cpr_is_locked_after_ten_failures_from_any_ip()
    {
        // Distributed guessing: each failure from a different IP, so the per-IP limits never trigger.
        for ($i = 1; $i <= 9; $i++) {
            $this->login(['password' => 'wrong'], "10.1.0.$i")
                ->assertStatus(422)
                ->assertJsonPath('attempts_left', 10 - $i);
        }

        $this->login(['password' => 'wrong'], '10.1.0.10')
            ->assertStatus(429)
            ->assertHeader('Retry-After', config('portal.login.lockout_seconds'));

        // Even the right password is refused while locked.
        $this->login([], '10.1.0.11')->assertStatus(429)->assertJsonStructure(['message', 'retry_after']);
        $this->assertGuest('student');
    }

    public function test_successful_login_resets_the_failure_count()
    {
        $this->login(['password' => 'wrong'], '10.2.0.1')->assertJsonPath('attempts_left', 9);
        $this->login([], '10.2.0.2')->assertOk();
        $this->postJson('/api/v1/auth/logout');

        $this->login(['password' => 'wrong'], '10.2.0.3')->assertJsonPath('attempts_left', 9);
    }

    public function test_requests_per_cpr_and_ip_are_rate_limited()
    {
        for ($i = 0; $i < 5; $i++) {
            $this->login(['password' => 'wrong'])->assertStatus(422);
        }

        $this->login()
            ->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertJsonPath('message', __('portal.auth.throttled'));
    }

    public function test_filled_honeypot_fails_like_a_wrong_password()
    {
        $this->login(['website' => 'http://spam.example'])
            ->assertStatus(422)
            ->assertJsonPath('message', __('portal.auth.failed'));

        $this->assertGuest('student');
    }

    public function test_turnstile_is_verified_before_credentials()
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake(['challenges.cloudflare.com/*' => Http::sequence()
            ->push(['success' => false])
            ->push(['success' => true])]);

        $this->login(['turnstile_token' => 'bad'], '10.3.0.1')
            ->assertStatus(422)
            ->assertJsonValidationErrors('turnstile_token');
        $this->assertGuest('student');

        $this->login(['turnstile_token' => 'good'], '10.3.0.2')->assertOk();

        Http::assertSent(function ($request) {
            return $request['secret'] === 'test-secret' && $request['remoteip'] === '10.3.0.2';
        });
    }

    public function test_missing_turnstile_token_fails_when_enabled()
    {
        config(['services.turnstile.secret' => 'test-secret']);
        Http::fake();

        $this->login()->assertStatus(422)->assertJsonValidationErrors('turnstile_token');
        Http::assertNothingSent();
    }

    public function test_soft_deleted_student_cannot_log_in()
    {
        $this->student->delete();

        $this->login()->assertStatus(422)->assertJsonPath('message', __('portal.auth.failed'));
    }

    public function test_login_without_a_stateful_session_is_refused()
    {
        $this->withHeaders(['Referer' => 'https://evil.example/'])
            ->login()
            ->assertStatus(419)
            ->assertJsonPath('message', __('portal.errors.session_expired'));
    }
}
