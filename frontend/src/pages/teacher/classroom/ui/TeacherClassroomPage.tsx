import { CalendarDays, Info, Mail, Users } from 'lucide-react';
import { useState } from 'react';
import { Link, useParams } from 'react-router';
import { SessionCard, useClassroomSessionsQuery } from '@/entities/class-session';
import { useTeacherClassroomQuery } from '@/entities/classroom';
import { ClassroomStudents } from '@/widgets/classroom-students';
import { routes } from '@/shared/config';
import { t, tList } from '@/shared/i18n';
import { fmtDate, fmtTime, pct } from '@/shared/lib';
import { ErrorCard, LoadingBlocks } from '@/shared/ui';

type Tab = 'students' | 'sessions' | 'info';

/** /teacher/classes/:id (PLAN.md §8 screen 5): students (+ photo sheet), sessions, info. */
export function TeacherClassroomPage() {
  const id = Number(useParams().id);
  const { data: classroom, isLoading, isError, refetch } = useTeacherClassroomQuery(id);
  const sessions = useClassroomSessionsQuery(id);
  const [tab, setTab] = useState<Tab>('students');

  if (isLoading) return <LoadingBlocks />;
  if (isError || !classroom) return <ErrorCard onRetry={refetch} />;

  const days = tList('days.long');
  const all = sessions.data ?? [];
  // Pending first, then the next 2 upcoming, then recorded newest first (PLAN.md §8).
  const ordered = [
    ...all.filter((s) => s.status === 'pending' || s.status === 'open'),
    ...all.filter((s) => s.status === 'upcoming').slice(0, 2),
    ...all.filter((s) => s.status === 'recorded').reverse(),
  ];

  const tabs: { id: Tab; label: string; icon: typeof Users }[] = [
    { id: 'students', label: t('teacher.tabStudents'), icon: Users },
    { id: 'sessions', label: t('teacher.tabSessions'), icon: CalendarDays },
    { id: 'info', label: t('teacher.tabInfo'), icon: Info },
  ];

  return (
    <div className="space-y-4">
      <section className="hero-band bg-pattern rounded-box p-5 pb-8">
        <h2 className="font-display text-2xl">{t('teacher.room', { room: classroom.number ?? '' })}</h2>
        <p className="text-sm opacity-85">{classroom.subjects.map((s) => s.name).join('، ')}</p>
        {classroom.name && <p className="text-xs opacity-80 mt-1">{classroom.name}</p>}
        <p className="text-xs opacity-80 mt-2 num">
          {t('teacher.students', { n: classroom.students.length })} · {t('teacher.rate', { rate: classroom.rate === null ? '—' : pct(classroom.rate) })}
        </p>
        <Link to={`${routes.teacher.messages}/new?room=${classroom.id}`} className="btn btn-sm mt-3 bg-white/15 border-white/30 text-white">
          <Mail className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('teacher.messageClass')}
        </Link>
      </section>

      <section className="card-soft">
        <div role="tablist" className="grid grid-cols-3 border-b border-base-300">
          {tabs.map(({ id: tabId, label, icon: Icon }) => (
            <button
              key={tabId}
              type="button"
              role="tab"
              aria-selected={tab === tabId}
              onClick={() => setTab(tabId)}
              className={`tab flex items-center justify-center h-12 gap-1 px-1 whitespace-nowrap ${tab === tabId ? 'tab-active text-primary font-semibold' : ''}`}
            >
              <Icon className="size-4" strokeWidth={1.75} aria-hidden="true" />
              {label}
            </button>
          ))}
        </div>
        <div className="p-4 sm:p-6" role="tabpanel">
          {tab === 'students' && <ClassroomStudents classroomId={classroom.id} students={classroom.students} />}
          {tab === 'sessions' &&
            (sessions.isLoading ? (
              <LoadingBlocks heights={['h-20', 'h-20']} />
            ) : (
              <div className="space-y-2">
                {ordered.map((session) => (
                  <SessionCard key={session.code} session={session} students={classroom.students.length} withDate />
                ))}
              </div>
            ))}
          {tab === 'info' && (
            <dl className="grid sm:grid-cols-2 gap-4 [&_dt]:text-[0.8125rem] [&_dt]:opacity-60 [&_dd]:font-medium">
              <div>
                <dt>{t('teacher.course')}</dt>
                <dd>{classroom.course.long_name}</dd>
              </div>
              <div>
                <dt>{t('teacher.place')}</dt>
                <dd>{classroom.name ?? '—'}</dd>
              </div>
              <div>
                <dt>{t('course.start')}</dt>
                <dd>{classroom.course.start_date ? fmtDate(classroom.course.start_date) : '—'}</dd>
              </div>
              <div>
                <dt>{t('course.end')}</dt>
                <dd>{classroom.course.end_date ? fmtDate(classroom.course.end_date) : '—'}</dd>
              </div>
              <div className="sm:col-span-2">
                <dt>{t('teacher.slots')}</dt>
                <dd>
                  <ul className="num">
                    {classroom.slots.map((slot, i) => (
                      <li key={i}>
                        {days[slot.day]} {slot.start ? fmtTime(slot.start) : ''}
                        {slot.end ? ` – ${fmtTime(slot.end)}` : ''}
                      </li>
                    ))}
                  </ul>
                </dd>
              </div>
            </dl>
          )}
        </div>
      </section>
    </div>
  );
}
