import { BookOpen } from 'lucide-react';
import { useMeQuery, ViewerAvatar } from '@/entities/viewer';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

/** P2.2 shell placeholder; the current course (hero, KPIs, tabs) arrives in P2.3. */
export function HomePage() {
  const { data: viewer } = useMeQuery();
  if (!viewer) return null;

  return (
    <div className="space-y-4">
      <section className="hero-band bg-pattern rounded-box p-5 pb-10 flex items-center gap-4">
        <ViewerAvatar viewer={viewer} className="w-14 h-[4.5rem]" textClassName="text-2xl" />
        <div className="min-w-0">
          <p className="text-sm opacity-80">{t('app.greeting')}</p>
          <p className="font-display text-xl truncate">{viewer.first_name}</p>
          <p className="text-xs opacity-80 num">{t('app.studentNumber', { id: viewer.student_id })}</p>
        </div>
      </section>
      <div className="card-soft">
        <EmptyState icon={BookOpen} title={t('pages.comingSoonTitle')} body={t('pages.homeIntro')} />
      </div>
    </div>
  );
}
