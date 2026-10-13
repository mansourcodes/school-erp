import { BellRing } from 'lucide-react';
import { useState } from 'react';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';
import { useEnablePush } from './NotificationsCard';

const ASKED_KEY = 'gh-push-asked';

function read(storage: Storage, key: string): string | null {
  try {
    return storage.getItem(key);
  } catch {
    return null;
  }
}

/**
 * "هل تريد تلقي الإشعارات؟" once, right after the first successful login (PLAN.md §4).
 * Only "تفعيل" triggers the browser permission dialog; "لاحقًا" leaves it to حسابي → الإشعارات.
 */
export function PushSoftPrompt({ afterLoginKey }: { afterLoginKey: string }) {
  const push = useEnablePush();
  const [open, setOpen] = useState(
    () => read(sessionStorage, afterLoginKey) === '1' && !read(localStorage, ASKED_KEY),
  );

  const visible = open && push.enabled && push.support === 'supported' && push.permission === 'default';
  const close = () => {
    try {
      localStorage.setItem(ASKED_KEY, '1');
    } catch {
      // Private mode.
    }
    setOpen(false);
  };

  return (
    <Dialog open={visible} onClose={close} labelledBy="push-prompt-title" closeLabel={t('notifications.later')}>
      <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))] text-center">
        <div className="sheet-grip" />
        <div className="arch w-14 h-16 bg-primary/10 text-primary grid place-items-center mx-auto mb-3">
          <BellRing className="size-6" strokeWidth={1.75} aria-hidden="true" />
        </div>
        <h2 id="push-prompt-title" className="font-display text-lg">
          {t('notifications.promptTitle')}
        </h2>
        <p className="text-sm opacity-75 mt-1">{t('notifications.enableBody')}</p>
        <div className="grid grid-cols-2 gap-2 mt-6">
          <button type="button" className="btn btn-lg" onClick={close}>
            {t('notifications.later')}
          </button>
          <button
            type="button"
            className="btn btn-primary btn-lg"
            disabled={push.busy}
            onClick={async () => {
              await push.enable();
              close();
            }}
          >
            {t('notifications.enable')}
          </button>
        </div>
      </div>
    </Dialog>
  );
}
