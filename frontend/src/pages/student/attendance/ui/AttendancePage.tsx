import { Calendar, CalendarCheck, List, RefreshCw, WifiOff } from 'lucide-react';
import { type CSSProperties, useMemo, useState } from 'react';
import { useSearchParams } from 'react-router';
import { CourseSelect, StatusChips, SubjectChips, useSelectedCourse } from '@/features/filter-attendance';
import { ALL_STATES, type AttendanceState, StatusPip, summarize, useAttendanceQuery } from '@/entities/attendance';
import { AttendanceCalendar } from '@/widgets/attendance-calendar';
import { AttendanceLog } from '@/widgets/attendance-log';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

type View = 'calendar' | 'list';

/** /attendance?course=&date= (PLAN.md §3.4). */
export function AttendancePage() {
  const [params] = useSearchParams();
  const selection = useSelectedCourse();
  const { courseId } = selection;
  const { data: sessions, isLoading, isError, refetch } = useAttendanceQuery(courseId, { skip: !courseId });

  const [subject, setSubject] = useState<number | null>(null);
  const [view, setView] = useState<View>('calendar');
  const [states, setStates] = useState<Set<AttendanceState>>(() => new Set(ALL_STATES));

  const subjects = useMemo(() => {
    const seen = new Map<number, string>();
    for (const s of sessions ?? []) seen.set(s.curriculum_id, s.curriculum);
    return [...seen].map(([id, name]) => ({ id, name }));
  }, [sessions]);
  const shown = (sessions ?? []).filter((s) => subject === null || s.curriculum_id === subject);
  const summary = summarize(shown);

  if (selection.isLoading) return <AttendanceSkeleton />;
  if (!courseId) {
    return (
      <div className="card-soft">
        <EmptyState icon={CalendarCheck} title={t('attendance.noCourseTitle')} body={t('attendance.noCourseBody')} />
      </div>
    );
  }

  return (
    <div className="space-y-4">
      <CourseSelect
        current={selection.current}
        archived={selection.archived}
        value={courseId}
        onChange={(id) => {
          setSubject(null);
          selection.setCourse(id);
        }}
      />
      <SubjectChips subjects={subjects} value={subject} onChange={setSubject} />

      {isLoading ? (
        <AttendanceSkeleton />
      ) : isError || !sessions ? (
        <div className="card-soft">
          <EmptyState
            icon={WifiOff}
            title={t('app.loadFailed')}
            body={t('course.errorBody')}
            action={
              <button type="button" className="btn btn-primary mt-2" onClick={() => refetch()}>
                <RefreshCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
                {t('app.retry')}
              </button>
            }
          />
        </div>
      ) : sessions.length === 0 ? (
        <div className="card-soft">
          <EmptyState icon={CalendarCheck} title={t('attendance.emptyTitle')} body={t('attendance.emptyBody')} />
        </div>
      ) : (
        <>
          <section className="card-soft p-4">
            <div className="flex items-center gap-3">
              <div
                className="radial-progress text-primary text-sm font-semibold num shrink-0"
                role="progressbar"
                aria-label={t('attendance.rate')}
                aria-valuenow={Math.round(summary.rate ?? 0)}
                style={{ '--value': Math.round(summary.rate ?? 0), '--size': '3.5rem', '--thickness': '5px' } as CSSProperties}
              >
                {summary.rate === null ? '—' : `${Math.round(summary.rate)}%`}
              </div>
              <div className="min-w-0 flex-1">
                <p className="text-xs opacity-70">{t('attendance.rate')}</p>
                <p className="font-semibold text-sm sm:text-base num">
                  {t('attendance.attendedOf', { attended: summary.attended, recorded: summary.recorded })}
                </p>
              </div>
              <div className="join shrink-0" role="group" aria-label={t('attendance.viewLabel')}>
                <button
                  type="button"
                  className={`btn join-item btn-square ${view === 'calendar' ? 'btn-active' : ''}`}
                  aria-pressed={view === 'calendar'}
                  aria-label={t('attendance.calendar')}
                  onClick={() => setView('calendar')}
                >
                  <Calendar className="size-5" strokeWidth={1.75} aria-hidden="true" />
                </button>
                <button
                  type="button"
                  className={`btn join-item btn-square ${view === 'list' ? 'btn-active' : ''}`}
                  aria-pressed={view === 'list'}
                  aria-label={t('attendance.list')}
                  onClick={() => setView('list')}
                >
                  <List className="size-5" strokeWidth={1.75} aria-hidden="true" />
                </button>
              </div>
            </div>
            <div className="flex flex-wrap gap-x-3 gap-y-2 mt-3 pt-3 border-t border-base-300" aria-label={t('attendance.legend')}>
              {ALL_STATES.map((state) => (
                <span key={state} className="inline-flex items-center gap-1.5 text-xs">
                  <StatusPip state={state} />
                  {t(`attendanceState.${state}`)}
                </span>
              ))}
            </div>
          </section>

          {view === 'calendar' ? (
            <AttendanceCalendar
              key={`${courseId}-${subject}`}
              sessions={shown}
              startDate={selection.course?.start_date ?? null}
              endDate={selection.course?.end_date ?? null}
              focusDate={params.get('date')}
            />
          ) : (
            <div className="space-y-3">
              <StatusChips value={states} onChange={setStates} />
              <AttendanceLog sessions={shown.filter((s) => states.has(s.state))} />
            </div>
          )}
        </>
      )}
    </div>
  );
}

function AttendanceSkeleton() {
  return (
    <div className="space-y-4" aria-busy="true">
      <div className="skeleton h-28 w-full rounded-box" />
      <div className="skeleton h-80 w-full rounded-box" />
      <span className="sr-only" role="status">
        {t('app.loading')}
      </span>
    </div>
  );
}
