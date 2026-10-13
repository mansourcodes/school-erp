import { ChevronLeft, Clock, MapPin, TriangleAlert, Users } from 'lucide-react';
import { Link } from 'react-router';
import { routes } from '@/shared/config';
import { t, tList } from '@/shared/i18n';
import { fmtTime, pct } from '@/shared/lib';
import type { TeacherClassroom } from '../model/types';

/** One of my classrooms (PLAN.md §8 "صفوفي"): subjects, place, weekly slots, students, rate, pending. */
export function ClassroomCard({ classroom }: { classroom: TeacherClassroom }) {
  const days = tList('days.long');

  return (
    <Link to={routes.teacher.classroom(classroom.id)} className="card-soft p-4 block active:bg-base-200 hover:bg-base-200/40 transition-colors">
      <div className="flex items-start gap-3">
        <div className="min-w-0 flex-1">
          <h2 className="font-display text-lg">{t('teacher.room', { room: classroom.number ?? '' })}</h2>
          <p className="text-sm opacity-80">{classroom.subjects.map((s) => s.name).join('، ')}</p>
          {classroom.name && (
            <p className="text-xs opacity-60 flex items-center gap-1 mt-1">
              <MapPin className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
              {classroom.name}
            </p>
          )}
        </div>
        <ChevronLeft className="size-5 opacity-40 shrink-0 mt-1" strokeWidth={1.75} aria-hidden="true" />
      </div>

      <ul className="flex flex-wrap gap-x-3 gap-y-1 mt-3 text-xs opacity-75">
        {classroom.slots.map((slot, i) => (
          <li key={i} className="inline-flex items-center gap-1 num">
            <Clock className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
            {days[slot.day]} {slot.start ? fmtTime(slot.start) : ''}
          </li>
        ))}
      </ul>

      <div className="flex items-center gap-4 mt-3 pt-3 border-t border-base-300 text-sm">
        <span className="inline-flex items-center gap-1.5 num">
          <Users className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
          {t('teacher.students', { n: classroom.students_count })}
        </span>
        <span className="num">{t('teacher.rate', { rate: classroom.rate === null ? '—' : pct(classroom.rate) })}</span>
        {classroom.pending_count > 0 && (
          <span className="badge badge-warning badge-sm gap-1 ms-auto num">
            <TriangleAlert className="size-3" strokeWidth={2} aria-hidden="true" />
            {classroom.pending_count} {t('teacher.statusPending')}
          </span>
        )}
      </div>
    </Link>
  );
}
