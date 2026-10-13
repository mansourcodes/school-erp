<?php

namespace App\Services\Portal;

use App\Exceptions\Portal\BotCheckFailedException;
use App\Exceptions\Portal\LoginFailedException;
use App\Exceptions\Portal\LoginLockedException;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Credential check for the portal login, in the order of PLAN.md §6:
 * honeypot → per-CPR lockout → Turnstile → credentials.
 */
class StudentLogin
{
    protected $lockout;

    protected $turnstile;

    public function __construct(LoginLockout $lockout, TurnstileVerifier $turnstile)
    {
        $this->lockout = $lockout;
        $this->turnstile = $turnstile;
    }

    /**
     * @throws LoginFailedException|LoginLockedException|BotCheckFailedException
     */
    public function attempt($cpr, $password, $honeypot, $turnstileToken, Request $request)
    {
        if ($honeypot !== null && $honeypot !== '') {
            usleep(config('portal.login.honeypot_delay_ms') * 1000);
            $this->log('portal.login.honeypot', $cpr, $request);

            throw new LoginFailedException($this->lockout->recordFailure($cpr));
        }

        $lockedFor = $this->lockout->lockedFor($cpr);
        if ($lockedFor > 0) {
            throw new LoginLockedException($lockedFor);
        }

        if (! $this->turnstile->verify($turnstileToken, $request->ip())) {
            $this->log('portal.login.bot_check_failed', $cpr, $request);

            throw new BotCheckFailedException();
        }

        $student = Student::where('cpr', $cpr)->first();

        // Hash an unknown CPR too, so its response time matches a wrong password.
        $valid = Hash::check($password, $student ? $student->password : $this->dummyHash());

        if (! $student || ! $valid) {
            $attemptsLeft = $this->lockout->recordFailure($cpr);
            $this->log($attemptsLeft === 0 ? 'portal.login.locked' : 'portal.login.failed', $cpr, $request);

            if ($attemptsLeft === 0) {
                throw new LoginLockedException(config('portal.login.lockout_seconds'));
            }

            throw new LoginFailedException($attemptsLeft);
        }

        $this->lockout->clear($cpr);

        return $student;
    }

    protected function dummyHash()
    {
        return Cache::rememberForever('portal-login:dummy-hash', function () {
            return Hash::make(Str::random(40));
        });
    }

    protected function log($event, $cpr, Request $request)
    {
        Log::channel('security')->notice($event, [
            'cpr_hash' => hash_hmac('sha256', (string) $cpr, config('app.key')),
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
        ]);
    }
}
