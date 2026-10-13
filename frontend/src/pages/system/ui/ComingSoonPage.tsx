import { Hourglass } from 'lucide-react';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

/** Stand-in for screens built in later milestones (P2.3–P2.6). */
export function ComingSoonPage() {
  return (
    <div className="card-soft">
      <EmptyState icon={Hourglass} title={t('pages.comingSoonTitle')} body={t('pages.comingSoonBody')} />
    </div>
  );
}
