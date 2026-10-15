<?php

namespace App\Services\Portal;

use App\Exceptions\Portal\BotCheckFailedException;
use App\Exceptions\Portal\LoginFailedException;
use App\Exceptions\Portal\LoginLockedException;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Credential check for the portal login, in the order of PLAN.md §6:
 * honeypot → per-account lockout → Turnstile → credentials.
 * Students sign in with their CPR (student guard), teachers with their username (web guard,
 * Spatie role "Teacher" required; other admin users cannot use the portal).
 */
class PortalLogin
{
    const GUARDS = ['student' => 'student', 'teacher' => 'web'];

    protected $lockout;

    protected $turnstile;

    public function __construct(LoginLockout $lockout, TurnstileVerifier $turnstile)
    {
        $this->lockout = $lockout;
        $this->turnstile = $turnstile;
    }

    /**
     * @param  string  $role  student | teacher
     * @param  string  $identifier  CPR (student) or username (teacher)
     * @return Student|User
     *
     * @throws LoginFailedException|LoginLockedException|BotCheckFailedException
     */
    public function attempt($role, $identifier, $password, $honeypot, $turnstileToken, Request $request)
    {
        $identifier = $role === 'teacher' ? Str::lower(trim($identifier)) : $identifier;
        $account = $role . '|' . $identifier;

        if ($honeypot !== null && $honeypot !== '') {
            usleep(config('portal.login.honeypot_delay_ms') * 1000);
            $this->log('portal.login.honeypot', $account, $request);

            throw new LoginFailedException($this->lockout->recordFailure($account), $role);
        }

        $lockedFor = $this->lockout->lockedFor($account);
        if ($lockedFor > 0) {
            throw new LoginLockedException($lockedFor);
        }

        if (! $this->turnstile->verify($turnstileToken, $request->ip())) {
            $this->log('portal.login.bot_check_failed', $account, $request);

            throw new BotCheckFailedException();
        }

        $user = $this->find($role, $identifier);

        // Hash an unknown account too, so its response time matches a wrong password.
        $valid = Hash::check($password, $user ? $user->password : $this->dummyHash());

        if (! $user || ! $valid) {
            $attemptsLeft = $this->lockout->recordFailure($account);
            $this->log($attemptsLeft === 0 ? 'portal.login.locked' : 'portal.login.failed', $account, $request);

            if ($attemptsLeft === 0) {
                throw new LoginLockedException(config('portal.login.lockout_seconds'));
            }

            throw new LoginFailedException($attemptsLeft, $role);
        }

        $this->lockout->clear($account);

        return $user;
    }

    protected function find($role, $identifier)
    {
        if ($role === 'student') {
            return Student::where('cpr', $identifier)->first();
        }

        $user = User::where('username', $identifier)->first();

        return $user && $user->hasRole('Teacher') ? $user : null;
    }

    protected function dummyHash()
    {
        return Cache::rememberForever('portal-login:dummy-hash', function () {
            return Hash::make(Str::random(40));
        });
    }

    protected function log($event, $account, Request $request)
    {
        Log::channel('security')->notice($event, [
            'account_hash' => hash_hmac('sha256', (string) $account, config('app.key')),
            'ip' => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 255),
        ]);
    }
}
