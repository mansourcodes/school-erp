import { ArrowUpRight, UserRound } from 'lucide-react';
import { LogoutButton } from '@/features/auth/logout';
import { ThemeSegment } from '@/features/theme-switch';
import { LEGACY_PORTAL_URL } from '@/shared/config';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

/**
 * P2.2 placeholder: the read-only profile cards arrive in P2.6. The account actions are
 * here already, because on phones the sidebar (theme, logout) is hidden.
 */
export function ProfilePage() {
  return (
    <div className="space-y-4">
      <div className="card-soft">
        <EmptyState icon={UserRound} title={t('pages.comingSoonTitle')} body={t('pages.comingSoonBody')} />
      </div>
      <section className="card-soft p-4 sm:p-6 space-y-4">
        <h2 className="font-display text-base">{t('theme.label')}</h2>
        <ThemeSegment />
        <div className="brand-rule" />
        <a href={LEGACY_PORTAL_URL} className="btn btn-ghost btn-block justify-start">
          <ArrowUpRight className="size-5" strokeWidth={1.75} aria-hidden="true" />
          {t('app.backToLegacy')}
        </a>
        <LogoutButton className="btn-block justify-start text-error" />
      </section>
    </div>
  );
}
