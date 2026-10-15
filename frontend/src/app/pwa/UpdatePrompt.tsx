import { RefreshCw } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Workbox } from 'workbox-window';
import { SW_SCOPE, SW_URL } from '@/shared/config';
import { t } from '@/shared/i18n';
import { useAppBase } from './use-app-base';

/**
 * Registers the service worker and offers new builds as "يتوفر تحديث جديد · تحديث".
 * The page reloads only on the user's tap, so nobody is interrupted mid-task (PLAN.md §4 "Updates").
 * The worker's URL and scope follow the page base (/beta/sw.js, or /sw.js once live), so it is registered
 * here at runtime rather than by vite-plugin-pwa, which fixes them at build time.
 */
export function UpdatePrompt() {
  const [workbox, setWorkbox] = useState<Workbox | null>(null);
  const [needRefresh, setNeedRefresh] = useState(false);
  // Only once the server confirmed this base, or the worker of the wrong one would be installed.
  const ready = useAppBase() === 'ok';

  useEffect(() => {
    if (!ready || !('serviceWorker' in navigator) || import.meta.env.DEV) return;
    const wb = new Workbox(SW_URL, { scope: SW_SCOPE });
    wb.addEventListener('waiting', () => setNeedRefresh(true));
    void wb.register();
    setWorkbox(wb);
  }, [ready]);

  const update = () => {
    if (!workbox) return;
    workbox.addEventListener('controlling', () => window.location.reload());
    workbox.messageSkipWaiting();
  };

  if (!needRefresh) return null;

  return (
    <div className="toast toast-bottom toast-center z-[70] mb-[calc(4.5rem+env(safe-area-inset-bottom))] lg:mb-0">
      <div role="status" className="alert shadow-lg">
        <RefreshCw className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
        <span>{t('pwa.updateAvailable')}</span>
        <button type="button" className="btn btn-primary btn-sm" onClick={update}>
          {t('pwa.update')}
        </button>
        <button type="button" className="btn btn-ghost btn-sm" onClick={() => setNeedRefresh(false)}>
          {t('pwa.later')}
        </button>
      </div>
    </div>
  );
}
