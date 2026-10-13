import { Building2 } from 'lucide-react';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';

interface PasswordHelpDialogProps {
  open: boolean;
  onClose: () => void;
}

/** No self-service reset by policy: visit the centre with an ID card (PLAN.md §3.1, Q5). */
export function PasswordHelpDialog({ open, onClose }: PasswordHelpDialogProps) {
  return (
    <Dialog open={open} onClose={onClose} labelledBy="password-help-title" closeLabel={t('passwordHelp.close')}>
      <div className="hero-band bg-pattern px-6 pt-6 pb-8 text-center">
        <div className="arch w-16 h-20 bg-white/15 mx-auto grid place-items-center mb-3">
          <Building2 className="size-7" strokeWidth={1.75} aria-hidden="true" />
        </div>
        <h2 id="password-help-title" className="font-display text-xl">
          {t('passwordHelp.title')}
        </h2>
      </div>
      <div className="p-6 space-y-4">
        <p className="leading-relaxed">{t('passwordHelp.body')}</p>
        <p className="text-xs opacity-60">{t('passwordHelp.note')}</p>
      </div>
      <div className="px-6 pb-6">
        <button type="button" className="btn btn-primary btn-block" onClick={onClose}>
          {t('passwordHelp.ok')}
        </button>
      </div>
    </Dialog>
  );
}
