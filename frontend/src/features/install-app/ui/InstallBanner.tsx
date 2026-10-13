import { Download, Share, SquarePlus, X } from 'lucide-react';
import { useState } from 'react';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';
import { useInstallPrompt } from '../model/use-install-prompt';

/** Small dismissible banner: Android installs natively, iOS opens a how-to sheet. */
export function InstallBanner() {
  const { mode, install, dismiss } = useInstallPrompt();
  const [iosSheet, setIosSheet] = useState(false);

  if (!mode) return null;

  return (
    <>
      <div className="card-soft p-3.5 mb-4 flex items-center gap-3 border-primary/40 bg-primary/10">
        <img src={`${import.meta.env.BASE_URL}pwa-64x64.png`} alt="" className="size-10 rounded-xl shrink-0" />
        <div className="flex-1 min-w-0">
          <p className="font-semibold text-sm">{t('pwa.installTitle')}</p>
          <p className="text-xs opacity-75">{t('pwa.installBody')}</p>
        </div>
        <button
          type="button"
          className="btn btn-primary btn-sm"
          onClick={() => (mode === 'android' ? void install() : setIosSheet(true))}
        >
          <Download className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('pwa.install')}
        </button>
        <button type="button" className="btn btn-ghost btn-sm btn-circle" aria-label={t('pwa.dismiss')} onClick={dismiss}>
          <X className="size-4" strokeWidth={1.75} aria-hidden="true" />
        </button>
      </div>

      <Dialog open={iosSheet} onClose={() => setIosSheet(false)} labelledBy="ios-install-title" closeLabel={t('pwa.close')}>
        <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
          <div className="sheet-grip" />
          <h2 id="ios-install-title" className="font-display text-lg mb-4">
            {t('pwa.iosTitle')}
          </h2>
          <ol className="space-y-3">
            <li className="flex items-center gap-3">
              <Share className="size-5 text-primary shrink-0" strokeWidth={1.75} aria-hidden="true" />
              {t('pwa.iosStep1')}
            </li>
            <li className="flex items-center gap-3">
              <SquarePlus className="size-5 text-primary shrink-0" strokeWidth={1.75} aria-hidden="true" />
              {t('pwa.iosStep2')}
            </li>
            <li className="flex items-center gap-3">
              <Download className="size-5 text-primary shrink-0" strokeWidth={1.75} aria-hidden="true" />
              {t('pwa.iosStep3')}
            </li>
          </ol>
          <button type="button" className="btn btn-block btn-lg mt-6" onClick={() => setIosSheet(false)}>
            {t('pwa.close')}
          </button>
        </div>
      </Dialog>
    </>
  );
}
