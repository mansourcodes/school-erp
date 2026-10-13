import { CalendarCheck, ChartPie, RefreshCw, WifiOff } from 'lucide-react';
import { Link } from 'react-router';
import { CourseSelect, useSelectedCourse } from '@/features/filter-attendance';
import { useAttendanceStatsQuery } from '@/entities/attendance';
import { AttendanceSummary } from '@/widgets/attendance-summary';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

/** /attendance/stats?course= (PLAN.md §3.5). Numbers come from the server (same service as the admin report). */
export function StatsPage() {
  const selection = useSelectedCourse();
  const { courseId } = selection;
  const { data: stats, isLoading, isError, refetch } = useAttendanceStatsQuery(courseId, { skip: !courseId });

  if (selection.isLoading) return <StatsSkeleton />;
  if (!courseId) {
    return (
      <div className="card-soft">
        <EmptyState icon={ChartPie} title={t('attendance.noCourseTitle')} body={t('attendance.noCourseBody')} />
      </div>
    );
  }

  return (
    <div className="space-y-5">
      <div className="flex flex-col sm:flex-row gap-3 sm:items-end">
        <CourseSelect current={selection.current} archived={selection.archived} value={courseId} onChange={selection.setCourse} />
        <Link to={`${routes.attendance}?course=${courseId}`} className="btn btn-ghost text-primary sm:ms-auto">
          <CalendarCheck className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('stats.toLog')}
        </Link>
      </div>

      {isLoading ? (
        <StatsSkeleton />
      ) : isError ? (
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
      ) : !stats || stats.recorded === 0 ? (
        <div className="card-soft">
          <EmptyState icon={ChartPie} title={t('stats.emptyTitle')} body={t('stats.emptyBody')} />
        </div>
      ) : (
        <AttendanceSummary stats={stats} startDate={selection.course?.start_date ?? null} />
      )}
    </div>
  );
}

function StatsSkeleton() {
  return (
    <div className="space-y-4" aria-busy="true">
      <div className="grid gap-4 lg:grid-cols-[1.1fr_2fr]">
        <div className="skeleton h-44" />
        <div className="skeleton h-44" />
      </div>
      <div className="skeleton h-40 w-full" />
      <span className="sr-only" role="status">
        {t('app.loading')}
      </span>
    </div>
  );
}
