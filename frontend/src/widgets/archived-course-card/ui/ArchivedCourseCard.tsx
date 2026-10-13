import { Archive, Award, BookMarked, ChevronDown } from 'lucide-react';
import { type ReactNode, useId } from 'react';
import type { CourseSummary } from '@/entities/course';
import { t } from '@/shared/i18n';
import { fmtDate } from '@/shared/lib';

interface ArchivedCourseCardProps {
  course: CourseSummary;
  open: boolean;
  onToggle: () => void;
  /** Rendered only while open, so the detail is fetched on demand. */
  children: ReactNode;
}

const MONTH_YEAR: Intl.DateTimeFormatOptions = { month: 'short', year: 'numeric' };

/** Archive list card: summary row that expands into the course detail (PLAN.md §3.3). */
export function ArchivedCourseCard({ course, open, onToggle, children }: ArchivedCourseCardProps) {
  const bodyId = useId();
  const when = [
    course.semester,
    course.hijri_year ? t('course.hijri', { year: course.hijri_year }) : null,
    course.start_date && course.end_date
      ? `${fmtDate(course.start_date, MONTH_YEAR)} – ${fmtDate(course.end_date, MONTH_YEAR)}`
      : null,
  ]
    .filter(Boolean)
    .join(' · ');

  return (
    <article className="card-soft overflow-hidden" id={`course-${course.id}`}>
      <button
        type="button"
        className="w-full text-start p-4 sm:p-5 flex gap-3 sm:gap-4 items-center active:bg-base-200 hover:bg-base-200/60 transition-colors"
        aria-expanded={open}
        aria-controls={bodyId}
        onClick={onToggle}
      >
        <div className="arch w-10 h-13 sm:w-12 sm:h-16 shrink-0 bg-base-200 grid place-items-center text-primary">
          <BookMarked className="size-5" strokeWidth={1.75} aria-hidden="true" />
        </div>
        <div className="flex-1 min-w-0">
          <div className="flex flex-wrap gap-2 items-center mb-1">
            <span className="badge badge-sm badge-ghost gap-1">
              <Archive className="size-3" strokeWidth={1.75} aria-hidden="true" />
              {t('archive.archived')}
            </span>
            {when && <span className="text-xs opacity-60">{when}</span>}
          </div>
          <h2 className="font-display text-base sm:text-lg leading-snug">{course.long_name}</h2>
          <p className="mt-2 text-sm inline-flex items-center gap-1.5">
            <Award className="size-4 text-accent" strokeWidth={1.75} aria-hidden="true" />
            {course.marks_published ? (
              <span className="num">
                {t('archive.result', { grade: course.final_grade ?? '', total: course.total_mark ?? '—' })}
              </span>
            ) : (
              <span className="opacity-70">{t('archive.noResult')}</span>
            )}
          </p>
        </div>
        <ChevronDown
          className={`size-5 opacity-60 transition-transform shrink-0 ${open ? 'rotate-180' : ''}`}
          strokeWidth={1.75}
          aria-hidden="true"
        />
      </button>
      {open && (
        <div id={bodyId} className="border-t border-base-300">
          {children}
        </div>
      )}
    </article>
  );
}
