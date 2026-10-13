import { CircleCheck, CircleX, Clock, ClockAlert, FileCheck, Hourglass, type LucideIcon } from 'lucide-react';
import type { AttendanceState } from '../model/types';

interface StateMeta {
  /** Code used by the admin `student_attend_list` legend. */
  code: string;
  icon: LucideIcon;
  /** `.st-*` colour class (status palette, never a series colour). */
  cls: string;
  /** Excused = hatch texture on the same hue. */
  excused: boolean;
}

/** Single source of the status encoding (PLAN.md §2). Labels: `attendanceState.*` in ar.json. */
export const ATTENDANCE_STATES: Record<AttendanceState, StateMeta> = {
  present: { code: '✓', icon: CircleCheck, cls: 'st-present', excused: false },
  late: { code: 'ت', icon: Clock, cls: 'st-late', excused: false },
  late_excused: { code: 'تع', icon: ClockAlert, cls: 'st-late-excused', excused: true },
  absent: { code: '✗', icon: CircleX, cls: 'st-absent', excused: false },
  absent_excused: { code: 'غع', icon: FileCheck, cls: 'st-absent-excused', excused: true },
  unrecorded: { code: '—', icon: Hourglass, cls: 'st-none', excused: false },
};

/** Recorded states in display order. */
export const RECORDED_STATES: AttendanceState[] = ['present', 'late', 'late_excused', 'absent_excused', 'absent'];

export const ALL_STATES: AttendanceState[] = [...RECORDED_STATES, 'unrecorded'];
