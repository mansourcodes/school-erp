import { zodResolver } from '@hookform/resolvers/zod';
import { Eye, EyeOff, KeyRound, TriangleAlert } from 'lucide-react';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { toApiError } from '@/shared/api';
import { t } from '@/shared/i18n';
import { type PasswordOwner, useChangePasswordMutation } from '../api/change-password-api';
import { changePasswordSchema, type ChangePasswordValues } from '../model/change-password-schema';

const FIELDS = [
  { name: 'current_password', label: 'changePassword.current', autoComplete: 'current-password' },
  { name: 'password', label: 'changePassword.new', autoComplete: 'new-password' },
  { name: 'password_confirmation', label: 'changePassword.confirm', autoComplete: 'new-password' },
] as const;

/** Student / teacher profile: change the password after confirming the current one (server checks it too). */
export function ChangePasswordForm({ role }: { role: PasswordOwner }) {
  const [change, { isLoading }] = useChangePasswordMutation();
  const [showPassword, setShowPassword] = useState(false);
  const [status, setStatus] = useState<{ ok: boolean; text: string } | null>(null);
  const {
    register,
    handleSubmit,
    setError,
    setFocus,
    reset,
    formState: { errors },
  } = useForm<ChangePasswordValues>({
    resolver: zodResolver(changePasswordSchema),
    defaultValues: { current_password: '', password: '', password_confirmation: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setStatus(null);
    try {
      await change({ ...values, role }).unwrap();
      reset();
      setShowPassword(false);
      setStatus({ ok: true, text: t('changePassword.saved') });
    } catch (e) {
      const apiError = toApiError(e);
      const fieldErrors = apiError.status === 422 ? apiError.errors : undefined;
      if (fieldErrors?.current_password) {
        setError('current_password', { message: fieldErrors.current_password[0] });
        setFocus('current_password');
      } else if (fieldErrors?.password) {
        setError('password', { message: fieldErrors.password[0] });
        setFocus('password');
      } else {
        setStatus({ ok: false, text: apiError.status === 429 ? t('changePassword.throttled') : t('changePassword.failed') });
      }
    }
  });

  return (
    <form className="space-y-3" noValidate onSubmit={onSubmit}>
      {FIELDS.map((field) => {
        const error = errors[field.name];
        return (
          <fieldset key={field.name} className="fieldset p-0">
            <label className="fieldset-legend text-sm pb-1" htmlFor={field.name}>
              {t(field.label)}
            </label>
            <label className={`input input-lg sm:input-md w-full ${error ? 'input-error' : ''}`}>
              <KeyRound className="size-5 opacity-60" strokeWidth={1.75} aria-hidden="true" />
              <input
                id={field.name}
                type={showPassword ? 'text' : 'password'}
                autoComplete={field.autoComplete}
                dir="ltr"
                className="text-start"
                aria-invalid={!!error}
                disabled={isLoading}
                {...register(field.name)}
              />
              {field.name === 'current_password' && (
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
              )}
            </label>
            {error ? (
              <p className="label text-xs text-error" role="alert">
                <TriangleAlert className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                {error.message}
              </p>
            ) : (
              field.name === 'password' && <p className="label text-xs">{t('changePassword.hint')}</p>
            )}
          </fieldset>
        );
      })}

      <button type="submit" className="btn btn-primary btn-block btn-lg sm:btn-md" disabled={isLoading}>
        {isLoading && <span className="loading loading-spinner loading-sm" aria-hidden="true" />}
        {t('changePassword.submit')}
      </button>
      {status && (
        <p role={status.ok ? 'status' : 'alert'} className={`text-sm ${status.ok ? 'text-success' : 'text-error'}`}>
          {status.text}
        </p>
      )}
    </form>
  );
}
