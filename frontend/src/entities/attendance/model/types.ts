export type AttendanceState = 'present' | 'late' | 'late_excused' | 'absent' | 'absent_excused' | 'unrecorded';

/** GET /api/v1/student/attendance (PLAN.md §7.3 Session). */
export interface Session {
  date: string;
  start: string | null;
  end: string | null;
  curriculum_id: number;
  curriculum: string;
  teacher: string | null;
  state: AttendanceState;
  /** Recorded in another classroom of the course (the student was moved). */
  other_classroom: boolean;
}

/** GET /api/v1/student/attendance/stats (PLAN.md §7.3, §7.4). */
export interface AttendanceStats {
  counts: Record<AttendanceState, number>;
  recorded: number;
  attended: number;
  /** percentage, one decimal; null when nothing was recorded */
  rate: number | null;
  punctual: number | null;
}
