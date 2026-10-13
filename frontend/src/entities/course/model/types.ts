import type { Mark } from '@/entities/mark/@x/course';
import type { Payment } from '@/entities/payment/@x/course';

export interface Slot {
  /** 0 = Sunday … 6 = Saturday */
  day: number;
  /** `HH:mm`; null when the admin timetable has no time */
  start: string | null;
  end: string | null;
}

export interface CourseCurriculum {
  id: number;
  name: string;
  teacher: string | null;
  slots: Slot[];
}

/** GET /api/v1/student/courses?status=archived: one archive card. */
export interface CourseSummary {
  id: number;
  classroom_id: number;
  status: 'archived';
  long_name: string;
  academic_path: string | null;
  year: string | null;
  hijri_year: string | null;
  semester: string | null;
  start_date: string | null;
  end_date: string | null;
  class_room_number: string | null;
  marks_published: boolean;
  total_mark: number | null;
  final_grade: string | null;
}

/** GET /api/v1/student/courses/current and /student/courses/{id} (PLAN.md §7.3). */
export interface CourseDetail {
  id: number;
  classroom_id: number;
  status: 'active' | 'archived';
  long_name: string;
  academic_path: string | null;
  year: string | null;
  hijri_year: string | null;
  semester: string | null;
  start_date: string | null;
  end_date: string | null;
  duration: string | number | null;
  class_room_number: string | null;
  class_room_name: string | null;
  curricula: CourseCurriculum[];
  payments: Payment[];
  marks_published: boolean;
  marks: Mark[];
  total_mark: number | null;
  final_grade: string | null;
}
