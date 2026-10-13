import { ChartPie, Table2 } from 'lucide-react';
import { useState } from 'react';
import { type TeacherStats, useTeacherClassroomsQuery, useTeacherStatsQuery } from '@/entities/classroom';
import { AttendanceSummary } from '@/widgets/attendance-summary';
import { FollowUpList } from '@/widgets/follow-up-list';
import { t } from '@/shared/i18n';
import { pct } from '@/shared/lib';
import { EmptyState, ErrorCard, LoadingBlocks } from '@/shared/ui';

/** /teacher/stats (PLAN.md §8 screen 6): classroom chips, headline, breakdown, follow-up, per class, all students. */
export function TeacherStatsPage() {
  const classrooms = useTeacherClassroomsQuery();
  const [classroomId, setClassroomId] = useState<number | undefined>(undefined);
  const { data: stats, isLoading, isError, refetch } = useTeacherStatsQuery(classroomId);

  return (
    <div className="space-y-5">
      {(classrooms.data?.length ?? 0) > 1 && (
        <div className="rail" role="group" aria-label={t('teacher.navClasses')}>
          <button type="button" className="chip" aria-pressed={classroomId === undefined} onClick={() => setClassroomId(undefined)}>
            {t('teacher.statsAll')}
          </button>
          {classrooms.data?.map((c) => (
            <button key={c.id} type="button" className="chip" aria-pressed={classroomId === c.id} onClick={() => setClassroomId(c.id)}>
              {t('teacher.room', { room: c.number ?? '' })}
            </button>
          ))}
        </div>
      )}

      {isLoading ? (
        <LoadingBlocks heights={['h-44', 'h-40', 'h-40']} />
      ) : isError || !stats ? (
        <ErrorCard onRetry={refetch} />
      ) : stats.recorded === 0 ? (
        <div className="card-soft">
          <EmptyState icon={ChartPie} title={t('teacher.statsEmpty')} />
        </div>
      ) : (
        <>
          <AttendanceSummary stats={stats} startDate={null} />
          <FollowUpList students={stats.follow_up} />
          {classroomId === undefined && stats.classrooms.length > 1 && <PerClassroom classrooms={stats.classrooms} />}
          <AllStudents students={stats.students} />
        </>
      )}
    </div>
  );
}

/** Single-series bar per classroom (viz-1, not a status colour). */
function PerClassroom({ classrooms }: { classrooms: TeacherStats['classrooms'] }) {
  return (
    <section className="card-soft p-4 sm:p-6">
      <h2 className="font-display text-lg mb-3">{t('teacher.ratePerClass')}</h2>
      <ul className="space-y-3">
        {classrooms.map((c) => (
          <li key={c.id}>
            <div className="flex justify-between text-sm">
              <span>{t('teacher.room', { room: c.label.number ?? '' })}</span>
              <span className="num">{c.rate === null ? '—' : pct(c.rate)}</span>
            </div>
            <div className="bar-track mt-1">
              <div className="bar-fill" style={{ width: `${c.rate ?? 0}%` }} />
            </div>
          </li>
        ))}
      </ul>
    </section>
  );
}

/** Folded table of every student (PLAN.md §8 screen 6). */
function AllStudents({ students }: { students: TeacherStats['students'] }) {
  return (
    <details className="card-soft p-4 sm:p-6">
      <summary className="cursor-pointer font-display inline-flex items-center gap-2">
        <Table2 className="size-4 text-primary" strokeWidth={1.75} aria-hidden="true" />
        {t('teacher.allStudents')} <span className="num opacity-60">({students.length})</span>
      </summary>
      <div className="overflow-x-auto mt-3">
        <table className="table table-sm">
          <thead>
            <tr>
              <th>{t('teacher.studentCol')}</th>
              <th className="text-end">{t('teacher.rateCol')}</th>
              <th className="text-end">{t('teacher.absencesCol')}</th>
            </tr>
          </thead>
          <tbody>
            {students.map((s) => (
              <tr key={`${s.classroom_id}-${s.id}`}>
                <td>{s.name}</td>
                <td className="text-end num">{s.rate === null ? '—' : pct(s.rate)}</td>
                <td className="text-end num">{s.absences}</td>
              </tr>
            ))}
          </tbody>
        </table>
      </div>
    </details>
  );
}
