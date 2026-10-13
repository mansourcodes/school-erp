import { CalendarCheck, CalendarX, TriangleAlert } from 'lucide-react';
import { Link } from 'react-router';
import { SessionCard, type TeacherToday } from '@/entities/class-session';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { fmtDate, fmtTime } from '@/shared/lib';
import { EmptyState } from '@/shared/ui';

/** Teacher "اليوم" (PLAN.md §8 screen 2): unrecorded past sessions first, then today, then what's next. */
export function TodaySessions({ today }: { today: TeacherToday }) {
  return (
    <div className="space-y-5">
      {today.pending.length > 0 && (
        <section className="card-soft p-4 border-warning/50 bg-warning/10" aria-labelledby="pending-title">
          <h2 id="pending-title" className="font-display text-base flex items-center gap-2">
            <TriangleAlert className="size-5 text-warning" strokeWidth={1.75} aria-hidden="true" />
            {t('teacher.pendingTitle')}
          </h2>
          <p className="text-sm opacity-80 mt-1 num">{t('teacher.pendingBody', { n: today.pending.length })}</p>
          <ul className="mt-3 divide-y divide-base-300">
            {today.pending.slice(0, 5).map((session) => (
              <li key={session.code} className="py-2 flex items-center gap-3">
                <div className="min-w-0 flex-1">
                  <p className="font-medium text-sm">
                    {t('teacher.room', { room: session.classroom?.number ?? '' })} · {session.curriculum}
                  </p>
                  <p className="text-xs opacity-70 num">
                    {fmtDate(session.date, { weekday: 'long', day: 'numeric', month: 'short' })}
                    {session.start ? ` · ${fmtTime(session.start)}` : ''}
                  </p>
                </div>
                <Link to={routes.teacher.roster(session.code)} className="btn btn-warning btn-sm">
                  <CalendarCheck className="size-4" strokeWidth={1.75} aria-hidden="true" />
                  {t('teacher.recordNow')}
                </Link>
              </li>
            ))}
          </ul>
        </section>
      )}

      <section aria-labelledby="today-title" className="space-y-2">
        <h2 id="today-title" className="font-display text-lg">
          {t('teacher.todaySessions')}
        </h2>
        {today.today.length ? (
          today.today.map((session) => <SessionCard key={session.code} session={session} />)
        ) : (
          <div className="card-soft">
            <EmptyState icon={CalendarX} title={t('teacher.noSessionsToday')} />
          </div>
        )}
      </section>

      {today.upcoming.length > 0 && (
        <section aria-labelledby="upcoming-title" className="space-y-2">
          <h2 id="upcoming-title" className="font-display text-lg">
            {t('teacher.upcoming')}
          </h2>
          {today.upcoming.map((session) => (
            <SessionCard key={session.code} session={session} withDate />
          ))}
        </section>
      )}
    </div>
  );
}
