import { useEffect } from 'react';
import { usePortalInfoQuery } from '@/entities/branding';
import { APP_BASE, SW_SCOPE } from '@/shared/config';

export type AppBaseStatus = 'checking' | 'ok' | 'moving';

/**
 * Keeps the app at the server's page base (`base` of GET /api/v1/theme: '/beta/', or '/' once live).
 * A worker of the other base can still serve its cached shell there (after an admin switches
 * `portal.mode`), so the app drops that worker and moves to the same page at the right base.
 * Offline or on an error the app stays where it is.
 */
export function useAppBase(): AppBaseStatus {
  const { data, isError } = usePortalInfoQuery();
  const base = data?.base;
  const moving = typeof base === 'string' && base !== SW_SCOPE;

  useEffect(() => {
    if (typeof base !== 'string' || base === SW_SCOPE) return;
    const { pathname, search, hash } = window.location;
    const target = base.replace(/\/$/, '') + (pathname.slice(APP_BASE.length) || '/') + search + hash;
    void (async () => {
      const registration = await navigator.serviceWorker?.getRegistration(SW_SCOPE);
      // getRegistration() falls back to a wider scope; only this base's own worker goes.
      if (registration?.scope === new URL(SW_SCOPE, window.location.origin).href) await registration.unregister();
      window.location.replace(target);
    })();
  }, [base]);

  if (moving) return 'moving';
  return data || isError ? 'ok' : 'checking';
}
