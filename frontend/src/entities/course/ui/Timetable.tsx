import { Clock, UserRound } from 'lucide-react';
import { useState } from 'react';
import { t, tList } from '@/shared/i18n';
import { fmtTime } from '@/shared/lib';
import type { CourseCurriculum, Slot } from '../model/types';

interface TimetableProps {
  curricula: CourseCurriculum[];
  /** 0 = Sunday … 6 = Saturday; omitted for archived courses. */
  today?: number;
}

interface Entry {
  curriculum: CourseCurriculum;
  slot: Slot;
}

function groupByDay(curricula: CourseCurriculum[]): Map<number, Entry[]> {
  const days = new Map<number, Entry[]>();
  for (const curriculum of curricula) {
    for (const slot of curriculum.slots) {
      days.set(slot.day, [...(days.get(slot.day) ?? []), { curriculum, slot }]);
    }
  }
  for (const entries of days.values()) {
    entries.sort((a, b) => (a.slot.start ?? '99').localeCompare(b.slot.start ?? '99'));
  }
  return new Map([...days.entries()].sort(([a], [b]) => a - b));
}

function SessionList({ entries }: { entries: Entry[] }) {
  return (
    <ul className="space-y-3">
      {entries.map(({ curriculum, slot }, i) => (
        <li key={`${curriculum.id}-${i}`} className="flex gap-3">
          <div className="w-1 rounded-full bg-primary/70" />
          <div className="min-w-0 flex-1">
            <p className="font-medium leading-snug">{curriculum.name}</p>
            <p className="text-sm opacity-70 flex flex-wrap gap-x-3">
              <span className="inline-flex items-center gap-1">
                <Clock className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                <span className="num">
                  {slot.start ? `${fmtTime(slot.start)} – ${slot.end ? fmtTime(slot.end) : ''}` : t('course.noTime')}
                </span>
              </span>
              {curriculum.teacher && (
                <span className="inline-flex items-center gap-1">
                  {curriculum.teacher_photo_url ? (
                    <img src={curriculum.teacher_photo_url} alt="" className="arch w-5 h-6.5 object-cover shrink-0" loading="lazy" />
                  ) : (
                    <UserRound className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
                  )}
                  {curriculum.teacher}
                </span>
              )}
            </p>
          </div>
        </li>
      ))}
    </ul>
  );
}

/**
 * Phones: day chips with one day shown, today (or the next teaching day) preselected.
 * From `sm`: every day at once (PLAN.md §2 "Day-chip selector").
 */
export function Timetable({ curricula, today }: TimetableProps) {
  const byDay = groupByDay(curricula);
  const days = [...byDay.keys()];
  const longNames = tList('days.long');
  const shortNames = tList('days.short');

  const firstDay =
    today === undefined ? days[0] : (days.find((d) => d >= today) ?? days[0]);
  const [selected, setSelected] = useState(firstDay);

  if (days.length === 0) {
    return <p className="text-sm opacity-70">{t('course.noTimetable')}</p>;
  }
  const current = byDay.has(selected) ? selected : days[0];

  return (
    <>
      <div className="sm:hidden">
        <div
          className="grid gap-1.5 mb-3"
          style={{ gridTemplateColumns: `repeat(${days.length}, minmax(0, 1fr))` }}
          role="tablist"
          aria-label={t('days.label')}
        >
          {days.map((d) => (
            <button
              key={d}
              type="button"
              role="tab"
              aria-selected={d === current}
              onClick={() => setSelected(d)}
              className={`rounded-field min-h-12 py-1.5 flex flex-col items-center justify-center gap-0.5 text-sm transition-colors ${
                d === current ? 'bg-primary text-primary-content' : 'bg-base-200'
              }`}
            >
              {shortNames[d]}
              {d === today && <span className="text-[10px] leading-none">{t('days.today')}</span>}
            </button>
          ))}
        </div>
        <div className="card-soft p-4" role="tabpanel">
          <SessionList entries={byDay.get(current) ?? []} />
        </div>
      </div>

      <div className="hidden sm:grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
        {days.map((d) => (
          <section
            key={d}
            className={`card-soft p-4 ${d === today ? 'shadow-[0_0_0_2px_color-mix(in_oklab,var(--color-primary)_45%,transparent)]' : ''}`}
          >
            <header className="flex items-center justify-between mb-3">
              <h3 className="font-display text-base">{longNames[d]}</h3>
              {d === today && <span className="badge badge-primary badge-sm">{t('days.today')}</span>}
            </header>
            <SessionList entries={byDay.get(d) ?? []} />
          </section>
        ))}
      </div>
    </>
  );
}
