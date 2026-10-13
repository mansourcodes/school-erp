import { School } from 'lucide-react';
import { useTeacherTodayQuery } from '@/entities/class-session';
import { useMeQuery } from '@/entities/viewer';
import { TodaySessions } from '@/widgets/today-sessions';
import { t } from '@/shared/i18n';
import { fmtDate, fmtHijri, pct } from '@/shared/lib';
import { EmptyState, ErrorCard, LoadingBlocks } from '@/shared/ui';

/** /teacher (PLAN.md §8 screen 2). Refetches on focus: classes start while the phone sleeps. */
export function TeacherTodayPage() {
  const { data: viewer } = useMeQuery();
  const { data: today, isLoading, isError, refetch } = useTeacherTodayQuery(undefined, {
    refetchOnFocus: true,
    refetchOnReconnect: true,
  });

  if (isLoading || !viewer) return <LoadingBlocks />;
  if (isError || !today) return <ErrorCard onRetry={refetch} />;

  return (
    <div className="space-y-5">
      <section className="hero-band bg-pattern rounded-box p-5 pb-8">
        <p className="text-sm opacity-85">{t('teacher.greeting', { name: viewer.first_name })}</p>
        <p className="font-display text-xl sm:text-2xl mt-1">
          {fmtDate(today.date, { weekday: 'long', day: 'numeric', month: 'long' })}
        </p>
        <p className="text-xs sm:text-sm opacity-85">{fmtHijri(today.date)}</p>
        {today.course && <p className="text-xs opacity-80 mt-2">{t('teacher.courseLine', { name: today.course })}</p>}
      </section>

      {today.tiles.classrooms === 0 ? (
        <div className="card-soft">
          <EmptyState icon={School} title={t('teacher.noClasses')} body={t('teacher.noClassesBody')} />
        </div>
      ) : (
        <>
          <section className="grid grid-cols-2 lg:grid-cols-4 gap-3">
            <Tile label={t('teacher.tileClasses')} value={String(today.tiles.classrooms)} />
            <Tile label={t('teacher.tileStudents')} value={String(today.tiles.students)} />
            <Tile label={t('teacher.tileRate')} value={today.tiles.rate === null ? '—' : pct(today.tiles.rate)} />
            <Tile label={t('teacher.tilePending')} value={String(today.tiles.pending)} warn={today.tiles.pending > 0} />
          </section>
          <TodaySessions today={today} />
        </>
      )}
    </div>
  );
}

function Tile({ label, value, warn }: { label: string; value: string; warn?: boolean }) {
  return (
    <div className={`card-soft p-3.5 sm:p-4 ${warn ? 'border-warning/50 bg-warning/10' : ''}`}>
      <p className="text-xs sm:text-sm opacity-70">{label}</p>
      <p className="text-2xl sm:text-3xl font-semibold mt-1 num">{value}</p>
    </div>
  );
}
