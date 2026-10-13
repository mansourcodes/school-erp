export interface ClassroomSlot {
  curriculum_id: number;
  /** 0 = Sunday … 6 = Saturday */
  day: number;
  start: string | null;
  end: string | null;
}

/** GET /teacher/classrooms (one card). */
export interface TeacherClassroom {
  id: number;
  number: string | null;
  name: string | null;
  course: string;
  subjects: { id: number; name: string }[];
  slots: ClassroomSlot[];
  students_count: number;
  rate: number | null;
  pending_count: number;
}

/** One student of a classroom with their attendance summary (teacher subjects only). */
export interface ClassroomStudent {
  id: number;
  name: string;
  student_id: string;
  photo_url: string | null;
  rate: number | null;
  recorded: number;
  absences: number;
  late: number;
}

/** GET /teacher/classrooms/{id} */
export interface ClassroomDetail extends Omit<TeacherClassroom, 'course' | 'students_count' | 'pending_count'> {
  course: { id: number; long_name: string; start_date: string | null; end_date: string | null };
  students: ClassroomStudent[];
  readonly: boolean;
}

/** GET /teacher/stats?classroom_id= (PLAN.md §7.4 metrics + follow-up). */
export interface TeacherStats {
  counts: Record<'present' | 'late' | 'late_excused' | 'absent' | 'absent_excused' | 'unrecorded', number>;
  recorded: number;
  attended: number;
  rate: number | null;
  punctual: number | null;
  students: (ClassroomStudent & { classroom_id: number; classroom: { id: number; number: string | null; name: string | null } })[];
  follow_up: TeacherStats['students'];
  classrooms: { id: number; label: { id: number; number: string | null; name: string | null }; rate: number | null }[];
}

/** Rate below 80% or 3+ absences (PLAN.md §8 screen 6); shown in red on rows. */
export const LOW_RATE = 80;
