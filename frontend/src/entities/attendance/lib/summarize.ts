import type { AttendanceState, AttendanceStats, Session } from '../model/types';

const round1 = (value: number) => Math.round(value * 10) / 10;

/**
 * Same definitions as the server (PLAN.md §7.4): unrecorded sessions are excluded from every
 * rate; attendance = (present + late + late_excused) ÷ recorded; punctual = present ÷ recorded.
 * Used for client-side filtered views (one subject); full-course numbers come from /stats.
 */
export function summarize(sessions: Session[]): AttendanceStats {
  const counts: Record<AttendanceState, number> = {
    present: 0,
    late: 0,
    late_excused: 0,
    absent: 0,
    absent_excused: 0,
    unrecorded: 0,
  };
  for (const session of sessions) counts[session.state]++;

  const recorded = sessions.length - counts.unrecorded;
  const attended = counts.present + counts.late + counts.late_excused;

  return {
    counts,
    recorded,
    attended,
    rate: recorded ? round1((attended / recorded) * 100) : null,
    punctual: recorded ? round1((counts.present / recorded) * 100) : null,
  };
}
