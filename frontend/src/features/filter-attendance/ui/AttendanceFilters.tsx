import { BookOpen } from 'lucide-react';
import { ALL_STATES, type AttendanceState, StatusPip } from '@/entities/attendance';
import type { CourseDetail, CourseSummary } from '@/entities/course';
import { t } from '@/shared/i18n';

interface CourseSelectProps {
  current: CourseDetail | null | undefined;
  archived: CourseSummary[];
  value: number | undefined;
  onChange: (courseId: number) => void;
}

/** Native select: opens the phone's own picker (PLAN.md §2 phone patterns). Current course marked ●. */
export function CourseSelect({ current, archived, value, onChange }: CourseSelectProps) {
  return (
    <label className="select w-full lg:max-w-md">
      <span className="label">
        <BookOpen className="size-4" strokeWidth={1.75} aria-hidden="true" />
      </span>
      <select aria-label={t('attendance.course')} value={value ?? ''} onChange={(event) => onChange(Number(event.target.value))}>
        {current && <option value={current.id}>{t('attendance.active', { name: current.long_name })}</option>}
        {archived.map((course) => (
          <option key={course.id} value={course.id}>
            {course.long_name}
          </option>
        ))}
      </select>
    </label>
  );
}

interface SubjectChipsProps {
  subjects: { id: number; name: string }[];
  value: number | null;
  onChange: (subjectId: number | null) => void;
}

/** Swipeable chip rail: one thumb, no dropdown. */
export function SubjectChips({ subjects, value, onChange }: SubjectChipsProps) {
  if (subjects.length < 2) return null;
  return (
    <div className="rail" role="group" aria-label={t('attendance.subject')}>
      <button type="button" className="chip" aria-pressed={value === null} onClick={() => onChange(null)}>
        {t('attendance.allSubjects')}
      </button>
      {subjects.map((subject) => (
        <button
          key={subject.id}
          type="button"
          className="chip"
          aria-pressed={value === subject.id}
          onClick={() => onChange(subject.id)}
        >
          {subject.name}
        </button>
      ))}
    </div>
  );
}

interface StatusChipsProps {
  value: Set<AttendanceState>;
  onChange: (states: Set<AttendanceState>) => void;
}

/** Toggle states in or out of the list view. */
export function StatusChips({ value, onChange }: StatusChipsProps) {
  const toggle = (state: AttendanceState) => {
    const next = new Set(value);
    if (next.has(state)) next.delete(state);
    else next.add(state);
    onChange(next);
  };
  return (
    <div className="rail" role="group" aria-label={t('attendance.stateFilter')}>
      {ALL_STATES.map((state) => (
        <button key={state} type="button" className="chip" aria-pressed={value.has(state)} onClick={() => toggle(state)}>
          <StatusPip state={state} />
          {t(`attendanceState.${state}`)}
        </button>
      ))}
    </div>
  );
}
