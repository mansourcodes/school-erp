import { Archive, CalendarCheck, Lock, RefreshCw, Search, SearchX, WifiOff } from 'lucide-react';
import { useEffect, useMemo, useState } from 'react';
import { Link, useParams } from 'react-router';
import { type CourseSummary, useArchivedCoursesQuery, useCourseQuery } from '@/entities/course';
import { type StudentViewer, useMeQuery } from '@/entities/viewer';
import { ArchivedCourseCard } from '@/widgets/archived-course-card';
import { CourseDetailsTabs, type CourseTab } from '@/widgets/course-details-tabs';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

/** /archive and /archive/:courseId (that card opens and scrolls into view). */
export function ArchivePage() {
  const { courseId } = useParams();
  const { data: viewer } = useMeQuery();
  const { data: courses, isLoading, isError, refetch } = useArchivedCoursesQuery();
  const [query, setQuery] = useState('');
  const [year, setYear] = useState('');
  const [openIds, setOpenIds] = useState<Set<number> | null>(null);

  // Open the linked course, else the newest one (as in the mockup).
  const linkedId = courseId ? Number(courseId) : null;
  const opened = openIds ?? new Set(courses?.length ? [linkedId ?? courses[0].id] : []);

  useEffect(() => {
    if (linkedId && courses) document.getElementById(`course-${linkedId}`)?.scrollIntoView({ block: 'start' });
  }, [linkedId, courses]);

  const years = useMemo(
    () => [...new Set((courses ?? []).map((c) => c.year).filter((y): y is string => !!y))].sort().reverse(),
    [courses],
  );
  const visible = (courses ?? []).filter(
    (c) => (!year || c.year === year) && (!query.trim() || c.long_name.includes(query.trim())),
  );

  if (isLoading || !viewer || viewer.role !== 'student') return <ArchiveSkeleton />;
  if (isError || !courses) {
    return (
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
    );
  }
  if (courses.length === 0) {
    return (
      <div className="card-soft">
        <EmptyState
          icon={Archive}
          title={t('archive.emptyTitle')}
          body={t('archive.emptyBody')}
          action={
            <Link to={routes.home} className="btn btn-primary btn-outline mt-2">
              {t('archive.backHome')}
            </Link>
          }
        />
      </div>
    );
  }

  const toggle = (id: number) => {
    const next = new Set(opened);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    setOpenIds(next);
  };

  return (
    <div className="space-y-5">
      <div className="flex flex-col sm:flex-row gap-2 sm:gap-3 sm:items-center">
        <label className="input w-full sm:max-w-xs">
          <Search className="size-4 opacity-60" strokeWidth={1.75} aria-hidden="true" />
          <input
            type="search"
            placeholder={t('archive.search')}
            aria-label={t('archive.searchLabel')}
            value={query}
            onChange={(event) => setQuery(event.target.value)}
          />
        </label>
        {years.length > 1 && (
          <select
            className="select w-full sm:w-44"
            aria-label={t('archive.year')}
            value={year}
            onChange={(event) => setYear(event.target.value)}
          >
            <option value="">{t('archive.allYears')}</option>
            {years.map((y) => (
              <option key={y} value={y}>
                {y}
              </option>
            ))}
          </select>
        )}
        <p className="text-sm opacity-60 sm:ms-auto flex items-center gap-1.5">
          <Lock className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('archive.readOnly')}
        </p>
      </div>

      {visible.length === 0 ? (
        <div className="card-soft">
          <EmptyState icon={SearchX} title={t('archive.noMatchTitle')} body={t('archive.noMatchBody')} />
        </div>
      ) : (
        <div className="space-y-4">
          {visible.map((course) => (
            <ArchivedCourseCard key={course.id} course={course} open={opened.has(course.id)} onToggle={() => toggle(course.id)}>
              <ArchivedCourseBody course={course} viewer={viewer} />
            </ArchivedCourseCard>
          ))}
        </div>
      )}
    </div>
  );
}

const ARCHIVE_TABS: CourseTab[] = ['marks', 'timetable', 'payments'];

/** Fetched when its card opens; read-only (no "today" in the timetable). */
function ArchivedCourseBody({ course, viewer }: { course: CourseSummary; viewer: StudentViewer }) {
  const { data: detail, isLoading, isError, refetch } = useCourseQuery(course.id);
  const [tab, setTab] = useState<CourseTab>('marks');

  if (isLoading) {
    return (
      <div className="p-4 sm:p-6 space-y-3" aria-busy="true">
        <div className="skeleton h-10 w-full" />
        <div className="skeleton h-40 w-full" />
        <span className="sr-only" role="status">
          {t('archive.loadingDetail')}
        </span>
      </div>
    );
  }
  if (isError || !detail) {
    return (
      <EmptyState
        icon={WifiOff}
        title={t('app.loadFailed')}
        action={
          <button type="button" className="btn btn-primary btn-sm mt-2" onClick={() => refetch()}>
            {t('app.retry')}
          </button>
        }
      />
    );
  }

  return (
    <>
      <CourseDetailsTabs course={detail} viewer={viewer} tab={tab} onTabChange={setTab} order={ARCHIVE_TABS} />
      <div className="px-4 pb-4 sm:px-6 sm:pb-6">
        <Link to={`${routes.attendance}?course=${course.id}`} className="btn btn-outline btn-primary btn-block sm:w-auto">
          <CalendarCheck className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('archive.attendanceLink')}
        </Link>
      </div>
    </>
  );
}

function ArchiveSkeleton() {
  return (
    <div className="space-y-4" aria-busy="true">
      <div className="skeleton h-12 w-full max-w-md" />
      <div className="skeleton h-32 w-full rounded-box" />
      <div className="skeleton h-32 w-full rounded-box" />
      <div className="skeleton h-32 w-full rounded-box" />
      <span className="sr-only" role="status">
        {t('app.loading')}
      </span>
    </div>
  );
}
