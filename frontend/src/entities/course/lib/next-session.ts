import { parseIsoDate, toIsoDate } from '@/shared/lib';
import type { CourseDetail } from '../model/types';

export interface NextSession {
  date: string;
  /** 0 = today, 1 = tomorrow, … */
  dayOffset: number;
  curriculum: string;
  teacher: string | null;
  start: string;
  end: string | null;
}

const LOOKAHEAD_DAYS = 14;

function hhmm(date: Date): string {
  return `${String(date.getHours()).padStart(2, '0')}:${String(date.getMinutes()).padStart(2, '0')}`;
}

/**
 * The next session from the weekly timetable: today's session still running or later,
 * else the earliest one on the following days, within the course dates.
 * Slots without a start time are skipped.
 */
export function nextSession(course: Pick<CourseDetail, 'curricula' | 'start_date' | 'end_date'>, now: Date): NextSession | null {
  const start = course.start_date ? parseIsoDate(course.start_date) : null;
  const end = course.end_date ? parseIsoDate(course.end_date) : null;
  const nowTime = hhmm(now);

  for (let offset = 0; offset < LOOKAHEAD_DAYS; offset++) {
    const day = new Date(now.getFullYear(), now.getMonth(), now.getDate() + offset);
    if (end && day > end) return null;
    if (start && day < start) continue;

    const candidates = course.curricula
      .flatMap((curriculum) =>
        curriculum.slots
          .filter((slot) => slot.day === day.getDay() && slot.start !== null)
          .map((slot) => ({ curriculum, slot, start: slot.start as string })),
      )
      .filter(({ slot, start: s }) => offset > 0 || (slot.end ?? s) > nowTime)
      .sort((a, b) => a.start.localeCompare(b.start));

    if (candidates.length > 0) {
      const { curriculum, slot, start: s } = candidates[0];
      return {
        date: toIsoDate(day),
        dayOffset: offset,
        curriculum: curriculum.name,
        teacher: curriculum.teacher,
        start: s,
        end: slot.end,
      };
    }
  }

  return null;
}
