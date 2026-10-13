import { zodResolver } from '@hookform/resolvers/zod';
import {
  ChevronLeft,
  CircleX,
  Eye,
  EyeOff,
  GraduationCap,
  IdCard,
  Info,
  KeyRound,
  Lock,
  Mail,
  ShieldCheck,
  TriangleAlert,
  UsersRound,
  WifiOff,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { useForm } from 'react-hook-form';
import { ensureCsrfCookie, toApiError } from '@/shared/api';
import { TURNSTILE_SITE_KEY } from '@/shared/config';
import { t } from '@/shared/i18n';
import { fmtCountdown } from '@/shared/lib';
import type { Viewer } from '@/entities/viewer';
import { type LoginRequest, useLoginMutation } from '../api/login-api';
import { useCountdown } from '../lib/use-countdown';
import { CPR_MAX_LENGTH, type LoginRole, loginSchema, type LoginValues } from '../model/login-schema';
import { Turnstile, type TurnstileHandle } from './Turnstile';

type Alert =
  | { kind: 'failed'; attemptsLeft?: number; role: LoginRole }
  | { kind: 'bot' }
  | { kind: 'network' }
  | { kind: 'unexpected' };

interface LoginFormProps {
  /** Arrived here because the session timed out (?expired=1). */
  expired?: boolean;
  /** The page routes by `viewer.role` (student home or teacher today). */
  onSuccess: (viewer: Viewer) => void;
  /** The page opens the password-help dialog (features never import each other). */
  onForgotPassword: () => void;
}

const DEFAULT_LOCK_SECONDS = 60;

// "…بعد {time} دقيقة." split around the placeholder, so the countdown can be bold.
const [lockedBefore, lockedAfter] = t('login.lockedBody', { time: '\u0000' }).split('\u0000');

export function LoginForm({ expired = false, onSuccess, onForgotPassword }: LoginFormProps) {
  const [login] = useLoginMutation();
  const [alert, setAlert] = useState<Alert | null>(null);
  const [lockedUntil, setLockedUntil] = useState<number | null>(null);
  const [showPassword, setShowPassword] = useState(false);
  const [capsLock, setCapsLock] = useState(false);
  const [turnstileToken, setTurnstileToken] = useState<string | null>(null);
  const turnstile = useRef<TurnstileHandle>(null);

  const secondsLocked = useCountdown(lockedUntil);
  const locked = secondsLocked > 0;

  const {
    register,
    handleSubmit,
    setError,
    setFocus,
    setValue,
    clearErrors,
    watch,
    formState: { errors, isSubmitting },
  } = useForm<LoginValues>({
    resolver: zodResolver(loginSchema),
    defaultValues: { role: 'student', cpr: '', email: '', password: '', website: '' },
  });
  const role = watch('role');
  const selectRole = (next: LoginRole) => {
    setValue('role', next);
    clearErrors();
    setAlert(null);
  };

  const cprField = register('cpr');
  const busy = isSubmitting || locked;
  const waitingForBotCheck = TURNSTILE_SITE_KEY !== '' && !turnstileToken;

  const submit = async (values: LoginValues) => {
    setAlert(null);
    const body: LoginRequest = {
      role: values.role,
      ...(values.role === 'teacher' ? { email: values.email.trim() } : { cpr: values.cpr }),
      password: values.password,
      website: values.website || undefined,
      turnstile_token: turnstileToken ?? undefined,
    };

    try {
      // One retry on 419: the CSRF cookie may have rotated since the page loaded.
      for (let attempt = 0; ; attempt++) {
        try {
          await ensureCsrfCookie();
          const viewer = await login(body).unwrap();
          onSuccess(viewer);
          return;
        } catch (error) {
          if (attempt === 0 && toApiError(error).status === 419) continue;
          throw error;
        }
      }
    } catch (error) {
      turnstile.current?.reset();
      const apiError = error instanceof TypeError ? { status: 'offline' as const } : toApiError(error);

      if (apiError.status === 429) {
        setLockedUntil(Date.now() + (apiError.retryAfter ?? DEFAULT_LOCK_SECONDS) * 1000);
      } else if (apiError.status === 422 && apiError.errors?.turnstile_token) {
        setAlert({ kind: 'bot' });
      } else if (apiError.status === 422 && apiError.attemptsLeft !== undefined) {
        setAlert({ kind: 'failed', attemptsLeft: apiError.attemptsLeft, role: values.role });
        setFocus('password');
      } else if (apiError.status === 422 && apiError.errors) {
        if (apiError.errors.cpr) setError('cpr', { message: apiError.errors.cpr[0] });
        if (apiError.errors.email) setError('email', { message: apiError.errors.email[0] });
        if (apiError.errors.password) setError('password', { message: apiError.errors.password[0] });
        if (!apiError.errors.cpr && !apiError.errors.email && !apiError.errors.password) setAlert({ kind: 'unexpected' });
      } else if (apiError.status === 'offline') {
        setAlert({ kind: 'network' });
      } else {
        setAlert({ kind: 'unexpected' });
      }
    }
  };

  return (
    <div className="card bg-base-100 shadow-xl border border-base-300">
      <div className="card-body p-5 sm:p-8 gap-5">
        <header>
          <h1 className="font-display text-2xl">{t('login.title')}</h1>
          <p className="text-sm opacity-70 mt-1">{t(role === 'teacher' ? 'login.introTeacher' : 'login.intro')}</p>
        </header>

        {/* One login screen for both roles (PLAN.md §3.1, §8). */}
        <div role="tablist" className="tabs tabs-box w-full grid grid-cols-2" aria-label={t('login.roleLabel')}>
          <button
            type="button"
            role="tab"
            className={`tab h-11 ${role === 'student' ? 'tab-active' : ''}`}
            aria-selected={role === 'student'}
            disabled={busy}
            onClick={() => selectRole('student')}
          >
            <GraduationCap className="size-4 me-1.5" strokeWidth={1.75} aria-hidden="true" />
            {t('login.student')}
          </button>
          <button
            type="button"
            role="tab"
            className={`tab h-11 ${role === 'teacher' ? 'tab-active' : ''}`}
            aria-selected={role === 'teacher'}
            disabled={busy}
            onClick={() => selectRole('teacher')}
          >
            <UsersRound className="size-4 me-1.5" strokeWidth={1.75} aria-hidden="true" />
            {t('login.teacher')}
          </button>
        </div>

        {locked ? (
          <div role="alert" className="alert alert-soft alert-warning items-start">
            <ShieldCheck className="size-5 mt-0.5" strokeWidth={1.75} aria-hidden="true" />
            <div>
              <p className="font-semibold">{t('login.lockedTitle')}</p>
              <p className="text-sm">
                {lockedBefore}
                <b className="num">{fmtCountdown(secondsLocked)}</b>
                {lockedAfter}
              </p>
            </div>
          </div>
        ) : alert ? (
          <LoginAlert alert={alert} />
        ) : expired ? (
          <div role="status" className="alert alert-soft alert-info">
            <Info className="size-5" strokeWidth={1.75} aria-hidden="true" />
            <span>{t('login.expired')}</span>
          </div>
        ) : null}

        <form className="flex flex-col gap-4" noValidate onSubmit={handleSubmit(submit)}>
          {role === 'teacher' && (
            <fieldset className="fieldset p-0">
              <label className="fieldset-legend text-sm pb-1" htmlFor="email">
                {t('login.email')}
              </label>
              <label className={`input input-lg w-full ${errors.email ? 'input-error' : ''}`}>
                <Mail className="size-5 opacity-60" strokeWidth={1.75} aria-hidden="true" />
                <input
                  id="email"
                  type="email"
                  inputMode="email"
                  enterKeyHint="next"
                  autoComplete="username"
                  dir="ltr"
                  className="text-start"
                  placeholder="name@example.com"
                  aria-invalid={!!errors.email}
                  aria-describedby="email-hint"
                  disabled={busy}
                  {...register('email')}
                  onKeyDown={(event) => {
                    if (event.key === 'Enter') {
                      event.preventDefault();
                      setFocus('password');
                    }
                  }}
                />
              </label>
              {errors.email ? (
                <p className="label text-xs text-error" role="alert">
                  <TriangleAlert className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                  {errors.email.message}
                </p>
              ) : (
                <p id="email-hint" className="label text-xs">
                  {t('login.emailHint')}
                </p>
              )}
            </fieldset>
          )}

          <fieldset className="fieldset p-0" hidden={role !== 'student'}>
            <label className="fieldset-legend text-sm pb-1" htmlFor="cpr">
              {t('login.cpr')}
            </label>
            <label className={`input input-lg w-full ${errors.cpr ? 'input-error' : ''}`}>
              <IdCard className="size-5 opacity-60" strokeWidth={1.75} aria-hidden="true" />
              <input
                id="cpr"
                type="text"
                inputMode="numeric"
                enterKeyHint="next"
                autoComplete="username"
                maxLength={CPR_MAX_LENGTH}
                dir="ltr"
                className="num tracking-widest text-start"
                aria-invalid={!!errors.cpr}
                aria-describedby="cpr-hint"
                disabled={busy}
                {...cprField}
                onChange={(event) => {
                  event.target.value = event.target.value.replace(/\D/g, '').slice(0, CPR_MAX_LENGTH);
                  return cprField.onChange(event);
                }}
                onKeyDown={(event) => {
                  if (event.key === 'Enter') {
                    event.preventDefault();
                    setFocus('password');
                  }
                }}
              />
            </label>
            {errors.cpr ? (
              <p className="label text-xs text-error" role="alert">
                <TriangleAlert className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                {errors.cpr.message}
              </p>
            ) : (
              <p id="cpr-hint" className="label text-xs">
                {t('login.cprHint')}
              </p>
            )}
          </fieldset>

          <fieldset className="fieldset p-0">
            <div className="flex items-center justify-between pb-1">
              <label className="fieldset-legend text-sm p-0" htmlFor="password">
                {t('login.password')}
              </label>
              <button
                type="button"
                className="link link-primary text-sm no-underline hover:underline"
                onClick={onForgotPassword}
              >
                {t('login.forgot')}
              </button>
            </div>
            <label className={`input input-lg w-full ${errors.password ? 'input-error' : ''}`}>
              <KeyRound className="size-5 opacity-60" strokeWidth={1.75} aria-hidden="true" />
              <input
                id="password"
                type={showPassword ? 'text' : 'password'}
                enterKeyHint="go"
                autoComplete="current-password"
                dir="ltr"
                className="text-start"
                placeholder="••••••••"
                aria-invalid={!!errors.password}
                disabled={busy}
                onKeyUp={(event) => setCapsLock(event.getModifierState?.('CapsLock') ?? false)}
                {...register('password')}
              />
              <button
                type="button"
                className="btn btn-ghost btn-sm btn-circle -me-2"
                aria-label={showPassword ? t('login.hidePassword') : t('login.showPassword')}
                aria-pressed={showPassword}
                onClick={() => setShowPassword((v) => !v)}
              >
                {showPassword ? (
                  <EyeOff className="size-4" strokeWidth={1.75} aria-hidden="true" />
                ) : (
                  <Eye className="size-4" strokeWidth={1.75} aria-hidden="true" />
                )}
              </button>
            </label>
            {errors.password && (
              <p className="label text-xs text-error" role="alert">
                <TriangleAlert className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                {errors.password.message}
              </p>
            )}
            {capsLock && (
              <p className="label text-xs">
                <TriangleAlert className="size-3.5 text-warning" strokeWidth={1.75} aria-hidden="true" />
                {t('login.capsLock')}
              </p>
            )}
          </fieldset>

          {/* Honeypot: invisible to people; bots that fill it get a generic failure (PLAN.md §6). */}
          <div aria-hidden="true" className="absolute -start-[9999px] w-px h-px overflow-hidden">
            <label>
              Website
              <input type="text" tabIndex={-1} autoComplete="off" {...register('website')} />
            </label>
          </div>

          {TURNSTILE_SITE_KEY && <Turnstile ref={turnstile} siteKey={TURNSTILE_SITE_KEY} onToken={setTurnstileToken} />}

          <button type="submit" className="btn btn-primary btn-lg btn-block mt-1" disabled={busy || waitingForBotCheck}>
            {isSubmitting ? (
              <span className="inline-flex items-center gap-2">
                <span className="loading loading-spinner loading-sm" aria-hidden="true" />
                {t('login.submitting')}
              </span>
            ) : locked ? (
              <span className="inline-flex items-center gap-2">
                <Lock className="size-5" strokeWidth={1.75} aria-hidden="true" />
                {t('login.lockedButton')}
              </span>
            ) : (
              <span className="inline-flex items-center gap-2">
                {t('login.submit')}
                <ChevronLeft className="size-5" strokeWidth={1.75} aria-hidden="true" />
              </span>
            )}
          </button>
        </form>

        <p className="text-xs opacity-60 flex items-center gap-2 justify-center">
          <ShieldCheck className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('login.secure')}
        </p>
      </div>
    </div>
  );
}

function LoginAlert({ alert }: { alert: Alert }) {
  switch (alert.kind) {
    case 'failed':
      return (
        <div role="alert" className="alert alert-soft alert-error">
          <CircleX className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>
            {t(alert.role === 'teacher' ? 'login.failedTeacher' : 'login.failed')}
            {alert.attemptsLeft !== undefined && <> {t('login.attemptsLeft', { n: alert.attemptsLeft })}</>}
          </span>
        </div>
      );
    case 'bot':
      return (
        <div role="alert" className="alert alert-soft alert-error">
          <ShieldCheck className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('login.botCheck')}</span>
        </div>
      );
    case 'network':
      return (
        <div role="alert" className="alert alert-soft alert-warning">
          <WifiOff className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('login.network')}</span>
        </div>
      );
    default:
      return (
        <div role="alert" className="alert alert-soft alert-error">
          <CircleX className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('login.unexpected')}</span>
        </div>
      );
  }
}
