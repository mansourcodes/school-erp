import { LogOut } from 'lucide-react';
import { useNavigate } from 'react-router';
import { baseApi, sessionEnded } from '@/shared/api';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { clearRosterQueue, useAppDispatch } from '@/shared/lib';
import { useLogoutMutation } from '../api/logout-api';

/** Personal data is never cached by the worker; this also drops anything a future route might add. */
async function purgeRuntimeCaches() {
  if (!('caches' in window)) return;
  const names = await caches.keys();
  await Promise.all(names.filter((name) => !name.startsWith('workbox-precache') && name !== 'fonts').map((name) => caches.delete(name)));
}

export function LogoutButton({ className = '' }: { className?: string }) {
  const [logout, { isLoading }] = useLogoutMutation();
  const dispatch = useAppDispatch();
  const navigate = useNavigate();

  const onClick = async () => {
    try {
      await logout().unwrap();
    } catch {
      // Already signed out or offline: clear this device anyway.
    }
    // Shared family phones: nothing of this student stays in memory (PLAN.md §4 PWA security).
    dispatch(sessionEnded());
    dispatch(baseApi.util.resetApiState());
    await purgeRuntimeCaches();
    // Queued attendance saves belong to this teacher; a shared phone must not send them later.
    clearRosterQueue();
    navigate(routes.login, { replace: true });
  };

  return (
    <button type="button" className={`btn btn-ghost ${className}`} onClick={onClick} disabled={isLoading}>
      {isLoading ? (
        <span className="loading loading-spinner loading-sm" aria-hidden="true" />
      ) : (
        <LogOut className="size-5" strokeWidth={1.75} aria-hidden="true" />
      )}
      {t('logout.action')}
    </button>
  );
}
