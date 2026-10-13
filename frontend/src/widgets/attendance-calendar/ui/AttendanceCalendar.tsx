import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useMemo, useState } from 'react';
import { type Session, SessionRow, StatusPip } from '@/entities/attendance';
import { t, tList } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';
import { fmtDate, fmtHijri, parseIsoDate, toIsoDate, useMediaQuery } from '@/shared/lib';

interface AttendanceCalendarProps {
  sessions: Session[];
  /** Course dates bound the month navigation. */
  startDate: string | null;
  endDate: string | null;
  /** Open on this day (e.g. from an attendance notification: ?date=). */
  focusDate?: string | null;
  today?: Date;
}

const monthOf = (date: Date) => new Date(date.getFullYear(), date.getMonth(), 1);

/**
 * Month grid (Sun → Sat), one coded pip per session. Selecting a day shows its sessions:
 * a side panel from `lg`, a bottom sheet on phones (PLAN.md §3.4).
 */
export function AttendanceCalendar({ sessions, startDate, endDate, focusDate, today = new Date() }: AttendanceCalendarProps) {
  const wide = useMediaQuery('(min-width: 1024px)');
  const byDate = useMemo(() => {
    const map = new Map<string, Session[]>();
    for (const s of sessions) map.set(s.date, [...(map.get(s.date) ?? []), s]);
    return map;
  }, [sessions]);

  const lastSession = sessions.length ? sessions[sessions.length - 1].date : null;
  const initial = focusDate ?? lastSession ?? startDate ?? toIsoDate(today);
  const [month, setMonth] = useState(() => monthOf(parseIsoDate(initial)));
  const [selected, setSelected] = useState<string | null>(focusDate ?? null);
  const [sheetOpen, setSheetOpen] = useState(!!focusDate && !wide);

  const firstMonth = startDate ? monthOf(parseIsoDate(startDate)) : null;
  const end = endDate && parseIsoDate(endDate) < today ? parseIsoDate(endDate) : today;
  const lastMonth = monthOf(end);
  const monthKey = toIsoDate(month).slice(0, 7);

  // Desktop panel: the latest day with sessions in this month, unless one was picked.
  const daysThisMonth = [...byDate.keys()].filter((d) => d.startsWith(monthKey));
  const shownDay = selected && selected.startsWith(monthKey) ? selected : (daysThisMonth.at(-1) ?? null);

  const go = (delta: number) => {
    setMonth(new Date(month.getFullYear(), month.getMonth() + delta, 1));
    setSelected(null);
  };
  const pick = (date: string) => {
    setSelected(date);
    if (!wide) setSheetOpen(true);
  };

  const daysInMonth = new Date(month.getFullYear(), month.getMonth() + 1, 0).getDate();
  const todayIso = toIsoDate(today);
  const shortDays = tList('days.short');

  return (
    <section className="grid gap-4 lg:grid-cols-[1fr_20rem]">
      <div className="card-soft p-2.5 sm:p-5">
        <header className="flex items-center justify-between mb-2">
          <button
            type="button"
            className="btn btn-ghost btn-circle"
            aria-label={t('attendance.prevMonth')}
            disabled={!!firstMonth && month <= firstMonth}
            onClick={() => go(-1)}
          >
            <ChevronRight className="size-5" strokeWidth={1.75} aria-hidden="true" />
          </button>
          <h2 className="font-display text-base sm:text-lg" aria-live="polite">
            {fmtDate(toIsoDate(month), { month: 'long', year: 'numeric' })}
          </h2>
          <button
            type="button"
            className="btn btn-ghost btn-circle"
            aria-label={t('attendance.nextMonth')}
            disabled={month >= lastMonth}
            onClick={() => go(1)}
          >
            <ChevronLeft className="size-5" strokeWidth={1.75} aria-hidden="true" />
          </button>
        </header>

        <div className="grid grid-cols-7 text-center text-[11px] sm:text-xs opacity-60 mb-1" aria-hidden="true">
          {shortDays.map((name) => (
            <div key={name}>{name}</div>
          ))}
        </div>
        <div className="grid grid-cols-7 gap-1 sm:gap-1.5">
          {Array.from({ length: month.getDay() }, (_, i) => (
            <div key={`pad-${i}`} />
          ))}
          {Array.from({ length: daysInMonth }, (_, i) => {
            const iso = toIsoDate(new Date(month.getFullYear(), month.getMonth(), i + 1));
            const list = byDate.get(iso) ?? [];
            const isToday = iso === todayIso;
            return (
              <button
                key={iso}
                type="button"
                disabled={list.length === 0}
                onClick={() => pick(iso)}
                aria-label={
                  list.length
                    ? t('attendance.daySessions', {
                        date: fmtDate(iso, { weekday: 'long', day: 'numeric', month: 'long' }),
                        n: list.length,
                      })
                    : undefined
                }
                aria-pressed={list.length ? wide && iso === shownDay : undefined}
                className={`min-h-12 aspect-square sm:aspect-auto sm:h-20 rounded-lg p-1 sm:p-1.5 flex flex-col items-center sm:items-stretch gap-0.5 sm:gap-1 text-sm transition-colors ${
                  list.length ? 'bg-base-200/70 active:bg-base-300 hover:bg-base-300/80 cursor-pointer' : 'opacity-50'
                } ${isToday ? 'is-today' : ''} ${wide && iso === shownDay ? 'outline-2 outline-primary' : ''}`}
              >
                <span className={`num text-xs sm:text-sm ${isToday ? 'font-bold text-primary' : ''}`}>{i + 1}</span>
                <span className="flex flex-wrap justify-center sm:justify-start gap-0.5 sm:gap-1">
                  {list.map((s, k) => (
                    <StatusPip key={k} state={s.state} />
                  ))}
                </span>
              </button>
            );
          })}
        </div>
        <p className="text-xs opacity-60 text-center mt-3 lg:hidden">{t('attendance.tapDay')}</p>
      </div>

      <aside className="card-soft p-5 hidden lg:block" aria-live="polite">
        <DayDetails date={shownDay} sessions={shownDay ? (byDate.get(shownDay) ?? []) : []} />
      </aside>

      {!wide && (
        <Dialog
          open={sheetOpen && !!selected}
          onClose={() => setSheetOpen(false)}
          labelledBy="day-sheet-title"
          closeLabel={t('attendance.close')}
        >
          <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
            <div className="sheet-grip" />
            <DayDetails date={selected} sessions={selected ? (byDate.get(selected) ?? []) : []} titleId="day-sheet-title" />
            <button type="button" className="btn btn-block btn-lg mt-6" onClick={() => setSheetOpen(false)}>
              {t('attendance.close')}
            </button>
          </div>
        </Dialog>
      )}
    </section>
  );
}

function DayDetails({ date, sessions, titleId }: { date: string | null; sessions: Session[]; titleId?: string }) {
  if (!date) {
    return <h3 className="font-display text-base">{t('attendance.pickDay')}</h3>;
  }
  return (
    <>
      <h3 id={titleId} className="font-display text-lg lg:text-base mb-1">
        {fmtDate(date, { weekday: 'long', day: 'numeric', month: 'long' })}
      </h3>
      <p className="text-xs opacity-60 mb-4 lg:mb-3">{fmtHijri(date)}</p>
      <ul className="space-y-4 lg:space-y-3">
        {sessions.map((session, i) => (
          <li key={i}>
            <SessionRow session={session} />
          </li>
        ))}
      </ul>
    </>
  );
}
