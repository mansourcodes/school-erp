import { ChevronLeft, UserCheck } from 'lucide-react';
import { Link } from 'react-router';
import type { TeacherStats } from '@/entities/classroom';
import { StudentPhoto } from '@/entities/student';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { pct } from '@/shared/lib';
import { EmptyState } from '@/shared/ui';

/** "طلاب يحتاجون متابعة": rate < 80% or ≥ 3 absences, worst first (PLAN.md §8 screen 6). */
export function FollowUpList({ students }: { students: TeacherStats['follow_up'] }) {
  return (
    <section className="card-soft p-4 sm:p-6" aria-labelledby="follow-up-title">
      <h2 id="follow-up-title" className="font-display text-lg">
        {t('teacher.followUp')}
      </h2>
      <p className="text-xs opacity-60 mb-3">{t('teacher.followUpRule')}</p>
      {students.length === 0 ? (
        <EmptyState icon={UserCheck} title={t('teacher.noFollowUp')} />
      ) : (
        <ul className="divide-y divide-base-300 -mx-2">
          {students.map((s) => (
            <li key={`${s.classroom_id}-${s.id}`}>
              <Link
                to={routes.teacher.classroom(s.classroom_id)}
                className="min-h-14 px-2 py-2 flex items-center gap-3 rounded-field active:bg-base-200 hover:bg-base-200/60"
              >
                <StudentPhoto name={s.name} photoUrl={s.photo_url} className="w-9 h-12" />
                <div className="min-w-0 flex-1">
                  <p className="font-medium truncate">{s.name}</p>
                  <p className="text-xs opacity-60">{t('teacher.room', { room: s.classroom.number ?? '' })}</p>
                </div>
                <div className="text-end text-xs num">
                  <p className="text-error font-semibold">{s.rate === null ? '—' : pct(s.rate)}</p>
                  <p className="opacity-70">{t('teacher.absences', { n: s.absences })}</p>
                </div>
                <ChevronLeft className="size-4 opacity-40" strokeWidth={1.75} aria-hidden="true" />
              </Link>
            </li>
          ))}
        </ul>
      )}
    </section>
  );
}
