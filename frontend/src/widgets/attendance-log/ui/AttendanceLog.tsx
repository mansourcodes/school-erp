import { SearchX } from 'lucide-react';
import { type Session, SessionRow } from '@/entities/attendance';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';
import { fmtDate, parseIsoDate, toIsoDate } from '@/shared/lib';

/** Sunday that starts the session's week. */
function weekOf(iso: string): string {
  const date = parseIsoDate(iso);
  return toIsoDate(new Date(date.getFullYear(), date.getMonth(), date.getDate() - date.getDay()));
}

/** Sessions grouped by week (sticky headers), newest first (PLAN.md §3.4). */
export function AttendanceLog({ sessions }: { sessions: Session[] }) {
  if (sessions.length === 0) {
    return (
      <div className="card-soft">
        <EmptyState icon={SearchX} title={t('attendance.noMatchTitle')} body={t('attendance.noMatchBody')} />
      </div>
    );
  }

  const groups: { week: string; items: Session[] }[] = [];
  for (const session of [...sessions].reverse()) {
    const week = weekOf(session.date);
    if (groups.at(-1)?.week !== week) groups.push({ week, items: [] });
    groups[groups.length - 1].items.push(session);
  }

  return (
    <div className="card-soft overflow-hidden">
      {groups.map((group) => (
        <section key={group.week}>
          <h3 className="sticky top-[calc(3.5rem+env(safe-area-inset-top))] lg:top-16 z-10 bg-base-200/95 backdrop-blur px-4 py-2 text-xs font-semibold opacity-80">
            {t('attendance.week', { date: fmtDate(group.week, { day: 'numeric', month: 'long' }) })}
          </h3>
          <ul className="divide-y divide-base-300 px-4">
            {group.items.map((session, i) => (
              <li key={`${session.date}-${session.curriculum_id}-${i}`} className="py-3">
                <SessionRow session={session} withDate />
              </li>
            ))}
          </ul>
        </section>
      ))}
    </div>
  );
}
