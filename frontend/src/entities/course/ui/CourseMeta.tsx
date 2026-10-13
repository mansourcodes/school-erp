import { BookMarked, Calendar, CalendarCheck, Hourglass, LayoutGrid, type LucideIcon, MapPin } from 'lucide-react';
import type { ReactNode } from 'react';
import { t } from '@/shared/i18n';
import { fmtDate, fmtHijri } from '@/shared/lib';
import type { CourseDetail } from '../model/types';

function Row({ icon: Icon, label, children, wide }: { icon: LucideIcon; label: string; children: ReactNode; wide?: boolean }) {
  return (
    <div className={`flex gap-3 items-start ${wide ? 'col-span-2' : ''}`}>
      <Icon className="size-4 mt-1.5 text-primary shrink-0" strokeWidth={1.75} aria-hidden="true" />
      <div>
        <dt className="text-[0.8125rem] opacity-60">{label}</dt>
        <dd className="font-medium">{children}</dd>
      </div>
    </div>
  );
}

function DateValue({ iso }: { iso: string | null }) {
  if (!iso) return <>—</>;
  return (
    <>
      {fmtDate(iso)}
      <span className="text-xs opacity-60 block">{fmtHijri(iso)}</span>
    </>
  );
}

/** Same facts as the admin student timetable (`student_table_template`). */
export function CourseMeta({ course }: { course: CourseDetail }) {
  return (
    <dl className="grid grid-cols-2 gap-x-4 sm:gap-x-6 gap-y-4">
      <Row icon={LayoutGrid} label={t('course.roomNumber')}>
        <span className="num">{course.class_room_number ?? '—'}</span>
      </Row>
      <Row icon={BookMarked} label={t('course.path')}>
        {course.academic_path ?? '—'}
      </Row>
      <Row icon={MapPin} label={t('course.place')} wide>
        {course.class_room_name ?? '—'}
      </Row>
      <Row icon={Calendar} label={t('course.start')}>
        <DateValue iso={course.start_date} />
      </Row>
      <Row icon={CalendarCheck} label={t('course.end')}>
        <DateValue iso={course.end_date} />
      </Row>
      <Row icon={Hourglass} label={t('course.hours')}>
        {/* Usually a number of hours, but the admin field is free text. */}
        {course.duration === null
          ? '—'
          : /^\d+$/.test(String(course.duration))
            ? t('course.hoursValue', { n: course.duration })
            : course.duration}
      </Row>
    </dl>
  );
}
