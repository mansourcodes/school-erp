import { zodResolver } from '@hookform/resolvers/zod';
import { KeyRound, TriangleAlert, UserRound } from 'lucide-react';
import { useState } from 'react';
import { useForm } from 'react-hook-form';
import { toApiError } from '@/shared/api';
import { t } from '@/shared/i18n';
import { useChangeTeacherUsernameMutation } from '../api/change-username-api';
import { changeUsernameSchema, type ChangeUsernameValues, USERNAME_MAX_LENGTH } from '../model/change-username-schema';

interface ChangeUsernameFormProps {
  /** The current sign-in username (from /me). */
  username: string;
}

/**
 * Teacher profile: change the sign-in username after confirming the current password (the server checks both,
 * lowercases the name and checks it is free).
 */
export function ChangeUsernameForm({ username }: ChangeUsernameFormProps) {
  const [change, { isLoading }] = useChangeTeacherUsernameMutation();
  const [status, setStatus] = useState<{ ok: boolean; text: string } | null>(null);
  const {
    register,
    handleSubmit,
    setError,
    setFocus,
    reset,
    formState: { errors, isDirty },
  } = useForm<ChangeUsernameValues>({
    resolver: zodResolver(changeUsernameSchema),
    defaultValues: { username, current_password: '' },
  });

  const onSubmit = handleSubmit(async (values) => {
    setStatus(null);
    try {
      const viewer = await change(values).unwrap();
      reset({ username: viewer.username, current_password: '' });
      setStatus({ ok: true, text: t('changeUsername.saved') });
    } catch (e) {
      const apiError = toApiError(e);
      const fieldErrors = apiError.status === 422 ? apiError.errors : undefined;
      if (fieldErrors?.current_password) {
        setError('current_password', { message: fieldErrors.current_password[0] });
        setFocus('current_password');
      } else if (fieldErrors?.username) {
        setError('username', { message: fieldErrors.username[0] });
        setFocus('username');
      } else {
        setStatus({ ok: false, text: apiError.status === 429 ? t('changeUsername.throttled') : t('changeUsername.failed') });
      }
    }
  });

  return (
    <form className="space-y-3" noValidate onSubmit={onSubmit}>
      <fieldset className="fieldset p-0">
        <label className="fieldset-legend text-sm pb-1" htmlFor="new-username">
          {t('changeUsername.label')}
        </label>
        <label className={`input input-lg sm:input-md w-full ${errors.username ? 'input-error' : ''}`}>
          <UserRound className="size-5 opacity-60" strokeWidth={1.75} aria-hidden="true" />
          <input
            id="new-username"
            type="text"
            autoComplete="username"
            autoCapitalize="none"
            autoCorrect="off"
            spellCheck={false}
            maxLength={USERNAME_MAX_LENGTH}
            dir="ltr"
            className="text-start"
            aria-invalid={!!errors.username}
            disabled={isLoading}
            {...register('username')}
          />
        </label>
        {errors.username ? (
          <p className="label text-xs text-error" role="alert">
            <TriangleAlert className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
            {errors.username.message}
          </p>
        ) : (
          <p className="label text-xs whitespace-normal">{t('changeUsername.hint')}</p>
        )}
      </fieldset>

      <fieldset className="fieldset p-0">
        <label className="fieldset-legend text-sm pb-1" htmlFor="username-current-password">
          {t('changeUsername.password')}
        </label>
        <label className={`input input-lg sm:input-md w-full ${errors.current_password ? 'input-error' : ''}`}>
          <KeyRound className="size-5 opacity-60" strokeWidth={1.75} aria-hidden="true" />
          <input
            id="username-current-password"
            type="password"
            autoComplete="current-password"
            dir="ltr"
            className="text-start"
            aria-invalid={!!errors.current_password}
            disabled={isLoading}
            {...register('current_password')}
          />
        </label>
        {errors.current_password && (
          <p className="label text-xs text-error" role="alert">
            <TriangleAlert className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
            {errors.current_password.message}
          </p>
        )}
      </fieldset>

      <button type="submit" className="btn btn-primary btn-block btn-lg sm:btn-md" disabled={isLoading || !isDirty}>
        {isLoading && <span className="loading loading-spinner loading-sm" aria-hidden="true" />}
        {t('changeUsername.submit')}
      </button>
      {status && (
        <p role={status.ok ? 'status' : 'alert'} className={`text-sm ${status.ok ? 'text-success' : 'text-error'}`}>
          {status.text}
        </p>
      )}
    </form>
  );
}
