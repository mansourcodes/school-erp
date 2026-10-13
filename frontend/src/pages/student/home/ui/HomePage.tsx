import { Archive, BookOpen, CalendarCheck, CircleCheck, GraduationCap, RefreshCw, Wallet, WifiOff } from 'lucide-react';
import { Link } from 'react-router';
import { type AttendanceStats, useAttendanceStatsQuery } from '@/entities/attendance';
import { type CourseDetail, useCurrentCourseQuery } from '@/entities/course';
import { marksTotals } from '@/entities/mark';
import { totalPaid } from '@/entities/payment';
import { useMeQuery } from '@/entities/viewer';
import { CourseDetailsTabs, type CourseTab, useCourseTab } from '@/widgets/course-details-tabs';
import { CourseHero } from '@/widgets/course-hero';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { money, pct } from '@/shared/lib';

export function HomePage() {
  const { data: viewer } = useMeQuery();
  // Current course refetches on focus/reconnect (PLAN.md §4).
  const { data: course, isLoading, isError, refetch } = useCurrentCourseQuery(undefined, {
    refetchOnFocus: true,
    refetchOnReconnect: true,
  });

  const { data: stats } = useAttendanceStatsQuery(undefined, { refetchOnFocus: true, refetchOnReconnect: true });
  const [tab, selectTab] = useCourseTab();

  if (isLoading || !viewer) return <HomeSkeleton />;
  if (isError && course === undefined) return <HomeError onRetry={refetch} />;
  if (!course) return <NoActiveCourse />;

  return (
    <div className="space-y-4 lg:space-y-6">
      <CourseHero course={course} viewer={viewer} now={new Date()} />
      <KpiTiles course={course} stats={stats ?? null} onOpenTab={selectTab} />
      <CourseDetailsTabs
        id="course-details"
        course={course}
        viewer={viewer}
        tab={tab}
        onTabChange={selectTab}
        today={new Date().getDay()}
        sticky
      />
    </div>
  );
}

interface KpiTilesProps {
  course: CourseDetail;
  stats: AttendanceStats | null;
  onOpenTab: (tab: CourseTab) => void;
}

/** 2×2 on phones, 4 across on desktop (PLAN.md §3.2). */
function KpiTiles({ course, stats, onOpenTab }: KpiTilesProps) {
  const marks = marksTotals(course.marks);
  const open = (tab: CourseTab) => {
    onOpenTab(tab);
    document.getElementById('course-details')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
  };

  return (
    <section className="grid grid-cols-2 lg:grid-cols-4 gap-3 lg:gap-4" aria-label={t('course.summary')}>
      <Link to={routes.stats} className="card-soft p-3.5 sm:p-4 active:bg-base-200">
        <p className="text-xs sm:text-sm opacity-70 flex items-center gap-1.5">
          <CalendarCheck className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('course.attendanceRate')}
        </p>
        <p className="text-2xl sm:text-3xl font-semibold mt-1.5 num">{stats?.rate != null ? pct(stats.rate) : '—'}</p>
        <div className="h-1.5 rounded-full bg-base-200 mt-2.5 overflow-hidden">
          <div className="h-full rounded-full" style={{ background: 'var(--viz-1)', width: `${stats?.rate ?? 0}%` }} />
        </div>
      </Link>
      <Link to={routes.attendance} className="card-soft p-3.5 sm:p-4 active:bg-base-200">
        <p className="text-xs sm:text-sm opacity-70 flex items-center gap-1.5">
          <CircleCheck className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('course.attended')}
        </p>
        <p className="text-2xl sm:text-3xl font-semibold mt-1.5 num">
          {stats?.attended ?? 0}
          <span className="text-sm opacity-50"> / {stats?.recorded ?? 0}</span>
        </p>
        <p className="text-xs opacity-60 mt-1.5 num">
          {t('course.attendedSub', {
            absent: (stats?.counts.absent ?? 0) + (stats?.counts.absent_excused ?? 0),
            late: (stats?.counts.late ?? 0) + (stats?.counts.late_excused ?? 0),
          })}
        </p>
      </Link>
      <button type="button" className="card-soft p-3.5 sm:p-4 text-start active:bg-base-200" onClick={() => open('payments')}>
        <p className="text-xs sm:text-sm opacity-70 flex items-center gap-1.5">
          <Wallet className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('course.paid')}
        </p>
        <p className="text-xl sm:text-3xl font-semibold mt-1.5 num whitespace-nowrap">{money(totalPaid(course.payments))}</p>
        <p className="text-xs opacity-60 mt-1.5 num">{t('course.receipts', { n: course.payments.length })}</p>
      </button>
      <button type="button" className="card-soft p-3.5 sm:p-4 text-start active:bg-base-200" onClick={() => open('marks')}>
        <p className="text-xs sm:text-sm opacity-70 flex items-center gap-1.5">
          <GraduationCap className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('course.marks')}
        </p>
        <p className="text-2xl sm:text-3xl font-semibold mt-1.5 num">
          {course.marks_published && course.total_mark !== null ? course.total_mark : marks.sum}
          <span className="text-sm opacity-50"> / {marks.max || 100}</span>
        </p>
        <p className="text-xs opacity-60 mt-1.5">
          {course.marks_published ? course.final_grade || t('course.marksFinal') : t('course.marksInProgress')}
        </p>
      </button>
    </section>
  );
}

function HomeSkeleton() {
  return (
    <div className="space-y-4" aria-busy="true">
      <div className="skeleton h-40 w-full rounded-box" />
      <div className="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div className="skeleton h-24" />
        <div className="skeleton h-24" />
        <div className="skeleton h-24" />
        <div className="skeleton h-24" />
      </div>
      <div className="skeleton h-72 w-full rounded-box" />
      <span className="sr-only" role="status">
        {t('course.loading')}
      </span>
    </div>
  );
}

function NoActiveCourse() {
  return (
    <div className="card-soft">
      <div className="flex flex-col items-center text-center py-12 px-6 gap-3">
        <div className="arch w-20 h-24 bg-base-200 grid place-items-center text-primary">
          <BookOpen className="size-8" strokeWidth={1.75} aria-hidden="true" />
        </div>
        <h2 className="font-display text-xl">{t('course.noneTitle')}</h2>
        <p className="text-sm opacity-70 max-w-md">{t('course.noneBody')}</p>
        <Link to={routes.archive} className="btn btn-primary btn-outline btn-lg sm:btn-md w-full sm:w-auto mt-2">
          <Archive className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('course.browseArchive')}
        </Link>
      </div>
    </div>
  );
}

function HomeError({ onRetry }: { onRetry: () => void }) {
  return (
    <div className="card-soft">
      <div className="flex flex-col items-center text-center py-12 px-6 gap-3">
        <div className="arch w-20 h-24 bg-error/10 grid place-items-center text-error">
          <WifiOff className="size-8" strokeWidth={1.75} aria-hidden="true" />
        </div>
        <h2 className="font-display text-xl">{t('app.loadFailed')}</h2>
        <p className="text-sm opacity-70 max-w-md">{t('course.errorBody')}</p>
        <button type="button" className="btn btn-primary btn-lg sm:btn-md w-full sm:w-auto mt-2" onClick={onRetry}>
          <RefreshCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('app.retry')}
        </button>
      </div>
    </div>
  );
}
