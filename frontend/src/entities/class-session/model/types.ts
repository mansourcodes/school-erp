/** Recordable states (the teacher never sets "unrecorded"). */
export type RosterState = 'present' | 'late' | 'late_excused' | 'absent' | 'absent_excused';

export type SessionStatus = 'recorded' | 'pending' | 'open' | 'upcoming';

export interface ClassroomLabel {
  id: number;
  number: string | null;
  name: string | null;
}

/** One expected session of a teacher's subject (PLAN.md §8 teacher API). */
export interface TeacherSession {
  /** date#HH:MM#classroom#curriculum ("--:--" for a slot without a time) */
  code: string;
  classroom_id: number;
  date: string;
  start: string | null;
  end: string | null;
  curriculum_id: number;
  curriculum: string;
  status: SessionStatus;
  recorded: boolean;
  /** Same-day recording any time; later days are read-only (Q9). */
  editable: boolean;
  counts: Record<RosterState, number> | null;
  updated_at: string | null;
  classroom?: ClassroomLabel;
}

export interface TeacherToday {
  date: string;
  course: string | null;
  today: TeacherSession[];
  pending: TeacherSession[];
  upcoming: TeacherSession[];
  tiles: { classrooms: number; students: number; rate: number | null; pending: number };
}

export interface RosterStudent {
  id: number;
  name: string;
  student_id: string;
  photo_url: string | null;
  state: RosterState | null;
}

/** GET /teacher/sessions/{code} */
export interface Roster {
  session: TeacherSession & { classroom: ClassroomLabel };
  students: RosterStudent[];
  previous_code: string | null;
  next_code: string | null;
}
