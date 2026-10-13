import { RefreshCw } from 'lucide-react';
import { useRegisterSW } from 'virtual:pwa-register/react';
import { t } from '@/shared/i18n';

/**
 * Registers the service worker and offers new builds as "يتوفر تحديث جديد · تحديث".
 * The page reloads only on the user's tap, so nobody is interrupted mid-task (PLAN.md §4 "Updates").
 */
export function UpdatePrompt() {
  const {
    needRefresh: [needRefresh, setNeedRefresh],
    updateServiceWorker,
  } = useRegisterSW();

  if (!needRefresh) return null;

  return (
    <div className="toast toast-bottom toast-center z-[70] mb-[calc(4.5rem+env(safe-area-inset-bottom))] lg:mb-0">
      <div role="status" className="alert shadow-lg">
        <RefreshCw className="size-5 text-primary" strokeWidth={1.75} aria-hidden="true" />
        <span>{t('pwa.updateAvailable')}</span>
        <button type="button" className="btn btn-primary btn-sm" onClick={() => updateServiceWorker(true)}>
          {t('pwa.update')}
        </button>
        <button type="button" className="btn btn-ghost btn-sm" onClick={() => setNeedRefresh(false)}>
          {t('pwa.later')}
        </button>
      </div>
    </div>
  );
}
