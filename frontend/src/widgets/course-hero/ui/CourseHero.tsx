import { type CourseDetail, nextSession } from '@/entities/course';
import { type StudentViewer, ViewerAvatar } from '@/entities/viewer';
import { t, tList } from '@/shared/i18n';
import { fmtTime, parseIsoDate } from '@/shared/lib';

interface CourseHeroProps {
  course: CourseDetail;
  viewer: StudentViewer;
  now: Date;
}

function whenLabel(dayOffset: number, date: string): string {
  if (dayOffset === 0) return t('days.today');
  if (dayOffset === 1) return t('days.tomorrow');
  return tList('days.long')[parseIsoDate(date).getDay()];
}

/** First thing on a phone: greeting, current course, next session (PLAN.md §2 content priority). */
export function CourseHero({ course, viewer, now }: CourseHeroProps) {
  const next = nextSession(course, now);
  const subtitle = [
    course.semester,
    course.hijri_year ? t('course.hijri', { year: course.hijri_year }) : null,
    course.class_room_number ? t('course.room', { room: course.class_room_number }) : null,
  ]
    .filter(Boolean)
    .join(' · ');

  return (
    <section className="hero-band bg-pattern rounded-box overflow-hidden">
      <div className="p-4 sm:p-7 lg:grid lg:grid-cols-[1fr_auto] lg:gap-6 lg:items-center">
        <div className="flex gap-3 sm:gap-5 items-center min-w-0">
          <ViewerAvatar viewer={viewer} className="w-14 h-[4.5rem] sm:w-20 sm:h-24" textClassName="text-2xl sm:text-3xl" />
          <div className="min-w-0">
            <p className="text-sm opacity-85">
              {t('app.greeting')} {viewer.first_name}
            </p>
            <span className="badge badge-sm border-0 bg-white/20 text-white gap-1.5 my-1">
              <span className="status status-success" aria-hidden="true" />
              {t('course.active')}
            </span>
            <h2 className="font-display text-lg sm:text-2xl leading-snug">{course.long_name}</h2>
            {subtitle && <p className="text-xs sm:text-sm opacity-85 mt-0.5">{subtitle}</p>}
          </div>
        </div>

        {next && (
          <div className="mt-4 lg:mt-0 rounded-field bg-white/10 border border-white/20 p-3 flex items-center gap-3 lg:w-72">
            <div className="rounded-field bg-white text-neutral px-2.5 py-1.5 text-center leading-tight shrink-0">
              <p className="text-[11px]">{whenLabel(next.dayOffset, next.date)}</p>
              <p className="font-bold num text-sm">{fmtTime(next.start)}</p>
            </div>
            <div className="min-w-0">
              <p className="text-[11px] opacity-80">{t('course.nextSession')}</p>
              <p className="font-semibold truncate">{next.curriculum}</p>
              {next.teacher && (
                <p className="text-xs flex items-center gap-1.5 min-w-0">
                  {next.teacherPhotoUrl && (
                    <img src={next.teacherPhotoUrl} alt="" className="arch w-5 h-6.5 object-cover shrink-0" />
                  )}
                  <span className="opacity-75 truncate">{next.teacher}</span>
                </p>
              )}
            </div>
          </div>
        )}
      </div>
    </section>
  );
}
