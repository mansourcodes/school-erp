import { CalendarCheck, Check, SquarePen, TriangleAlert } from 'lucide-react';
import { Link } from 'react-router';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { fmtDate, fmtTime, toIsoDate } from '@/shared/lib';
import type { TeacherSession } from '../model/types';

interface SessionCardProps {
  session: TeacherSession;
  /** Students in the classroom (tally base). */
  students?: number;
  /** Show "today / yesterday / weekday" above the time. */
  withDate?: boolean;
  now?: Date;
}

function dateLabel(date: string, now: Date): string {
  const today = toIsoDate(now);
  const yesterday = toIsoDate(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1));
  if (date === today) return t('teacher.today');
  if (date === yesterday) return t('teacher.yesterday');
  return fmtDate(date, { weekday: 'long', day: 'numeric', month: 'short' });
}

/**
 * One session (Today, classroom sessions): a pending or open session gets the full-width
 * primary action (one-handed, in class); recorded ones show the tally and "تعديل" (PLAN.md §8).
 */
export function SessionCard({ session, students, withDate = false, now = new Date() }: SessionCardProps) {
  const href = routes.teacher.roster(session.code);
  const counts = session.counts;
  const attended = counts ? counts.present + counts.late + counts.late_excused : 0;
  const absent = counts ? counts.absent + counts.absent_excused : 0;
  const primary = session.status === 'pending' || (session.status === 'open' && !session.recorded);

  const badge = {
    recorded: (
      <span className="badge badge-success badge-soft badge-sm gap-1">
        <Check className="size-3" strokeWidth={2} aria-hidden="true" />
        {t('teacher.statusRecorded')}
      </span>
    ),
    pending: (
      <span className="badge badge-warning badge-sm gap-1">
        <TriangleAlert className="size-3" strokeWidth={2} aria-hidden="true" />
        {t('teacher.statusPending')}
      </span>
    ),
    open: <span className="badge badge-primary badge-sm">{t('teacher.statusOpen')}</span>,
    upcoming: <span className="badge badge-ghost badge-sm">{t('teacher.statusUpcoming')}</span>,
  }[session.status];

  const action = primary ? (
    <Link to={href} className={`btn btn-block ${session.status === 'pending' ? 'btn-warning' : 'btn-primary'}`}>
      <CalendarCheck className="size-4" strokeWidth={1.75} aria-hidden="true" />
      {session.status === 'pending' ? t('teacher.recordNow') : t('teacher.record')}
    </Link>
  ) : session.recorded ? (
    <Link to={href} className="btn btn-sm btn-ghost text-primary">
      <SquarePen className="size-4" strokeWidth={1.75} aria-hidden="true" />
      {t('teacher.edit')}
    </Link>
  ) : (
    <Link to={href} className="btn btn-sm btn-ghost">
      {t('teacher.view')}
    </Link>
  );

  return (
    <article className={`card-soft p-3.5 ${session.status === 'open' ? 'is-today' : ''}`}>
      <div className="flex items-center gap-3">
        <div className={`rounded-field bg-base-200 ${withDate ? 'w-28' : 'w-20'} px-2 py-2 text-center leading-tight shrink-0`}>
          {withDate && <p className="text-[11px] opacity-70">{dateLabel(session.date, now)}</p>}
          <p className="font-bold num text-sm whitespace-nowrap">{session.start ? fmtTime(session.start) : '—'}</p>
        </div>
        <div className="min-w-0 flex-1">
          <div className="flex items-center gap-x-2 gap-y-1 flex-wrap">
            {session.classroom && (
              <p className="font-semibold">{t('teacher.room', { room: session.classroom.number ?? '' })}</p>
            )}
            {badge}
          </div>
          <p className="text-sm opacity-80 truncate">{session.curriculum}</p>
          {counts ? (
            <p className="text-xs opacity-70 mt-1 flex flex-wrap gap-x-2 num">
              <span>{t('teacher.attendedOf', { attended, total: attended + absent })}</span>
              <span>{t('teacher.absentN', { n: absent })}</span>
            </p>
          ) : (
            students !== undefined && <p className="text-xs opacity-70 mt-1 num">{t('teacher.students', { n: students })}</p>
          )}
        </div>
        {!primary && <div className="shrink-0">{action}</div>}
      </div>
      {primary && <div className="mt-3">{action}</div>}
    </article>
  );
}
