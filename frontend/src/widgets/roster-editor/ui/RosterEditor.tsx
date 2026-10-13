import { Check, ChevronLeft, ChevronRight, History, Info } from 'lucide-react';
import { useState } from 'react';
import { Link } from 'react-router';
import {
  canQueue,
  LeaveGuard,
  markQueued,
  QuickActions,
  SaveBar,
  StateSegment,
  useQueued,
  useRosterDraft,
  useSaveAttendanceMutation,
} from '@/features/mark-attendance';
import { ATTENDANCE_STATES, StatusChip } from '@/entities/attendance';
import type { Roster, RosterState } from '@/entities/class-session';
import { StudentPhoto } from '@/entities/student';
import { toApiError } from '@/shared/api';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { fmtDate, fmtHijri, fmtTime } from '@/shared/lib';

const TALLY: RosterState[] = ['present', 'late', 'late_excused', 'absent', 'absent_excused'];

/** The attendance roster task screen (PLAN.md §8 screen 3). */
export function RosterEditor({ roster }: { roster: Roster }) {
  const { session, students } = roster;
  const draft = useRosterDraft(students);
  const [save, { isLoading: saving }] = useSaveAttendanceMutation();
  const queued = useQueued(session.code);
  const [toast, setToast] = useState<{ text: string; kind: 'success' | 'info' | 'error' } | null>(null);
  const editable = session.editable;

  const show = (text: string, kind: 'success' | 'info' | 'error') => {
    setToast({ text, kind });
    window.setTimeout(() => setToast(null), 3200);
  };

  const onSave = async () => {
    try {
      const result = await save({ code: session.code, states: draft.payload() }).unwrap();
      show(result.created ? t('teacher.savedFirst') : t('teacher.savedEdit'), 'success');
    } catch (error) {
      const apiError = toApiError(error);
      if (apiError.status === 'offline' && canQueue()) {
        // The service worker keeps the request and sends it when the connection returns.
        markQueued(session.code);
        show(t('teacher.queued'), 'info');
      } else if (apiError.status === 422) {
        show(t('teacher.rosterChanged'), 'error');
      } else {
        show(t('teacher.saveFailed'), 'error');
      }
    }
  };

  const tally = Object.fromEntries(TALLY.map((s) => [s, 0])) as Record<RosterState, number>;
  for (const s of students) {
    const state = draft.draft[s.id];
    if (state) tally[state]++;
  }
  const hint = !draft.complete ? t('teacher.hintIncomplete') : session.recorded ? t('teacher.hintEdit') : t('teacher.hintFirst');

  return (
    <div className="space-y-3">
      <section className="card-soft p-4">
        <div className="flex items-start gap-3">
          <div className="min-w-0 flex-1">
            <p className="font-display text-2xl sm:text-3xl leading-tight">
              {fmtDate(session.date, { weekday: 'long', day: 'numeric', month: 'long' })}
            </p>
            <p className="text-sm opacity-75 mt-0.5">
              {fmtHijri(session.date)}
              {session.start && (
                <span className="num whitespace-nowrap">
                  {' · '}
                  {fmtTime(session.start)}
                  {session.end ? ` – ${fmtTime(session.end)}` : ''}
                </span>
              )}
            </p>
            <div className="flex items-center gap-2 flex-wrap mt-3">
              <h2 className="font-display text-lg">{t('teacher.room', { room: session.classroom.number ?? '' })}</h2>
              {session.recorded ? (
                <span className="badge badge-success badge-soft badge-sm gap-1">
                  <Check className="size-3" strokeWidth={2} aria-hidden="true" />
                  {t('teacher.statusRecorded')}
                </span>
              ) : !editable ? (
                <span className="badge badge-ghost badge-sm">{t('teacher.laterDay')}</span>
              ) : (
                <span className={`badge badge-sm ${session.status === 'pending' ? 'badge-warning' : 'badge-primary'}`}>
                  {session.status === 'pending' ? t('teacher.statusPending') : t('teacher.statusOpen')}
                </span>
              )}
            </div>
            <p className="text-sm opacity-80">
              {session.curriculum}
              {session.classroom.name ? ` · ${session.classroom.name}` : ''}
            </p>
          </div>
          <div className="join shrink-0">
            <SessionLink code={roster.previous_code} label={t('teacher.prevSession')} icon="prev" />
            <SessionLink code={roster.next_code} label={t('teacher.nextSession')} icon="next" />
          </div>
        </div>
        {session.updated_at && (
          <p className="text-xs opacity-60 mt-2 flex items-center gap-1.5 num">
            <History className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
            {t('teacher.lastEdited', {
              when: new Intl.DateTimeFormat('ar-u-nu-latn', { day: 'numeric', month: 'short', hour: 'numeric', minute: '2-digit' }).format(
                new Date(session.updated_at),
              ),
            })}
          </p>
        )}
      </section>

      {!editable && (
        <div role="note" className="alert alert-soft alert-info items-start">
          <Info className="size-5 mt-0.5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('teacher.readOnlyLater')}</span>
        </div>
      )}

      <div className="sticky top-[calc(3.5rem+env(safe-area-inset-top))] lg:top-16 z-20 -mx-4 px-4 py-2 bg-base-200/95 backdrop-blur space-y-2">
        {editable && <QuickActions onAll={draft.setAll} disabled={saving} />}
        <div className="flex flex-wrap gap-x-3 gap-y-1 text-xs num" aria-live="polite">
          {TALLY.map((state) => (
            <span key={state} className="inline-flex items-center gap-1">
              <span className={`size-2 rounded-full ${ATTENDANCE_STATES[state].cls}`} style={{ background: 'var(--c)' }} aria-hidden="true" />
              {t(`attendanceState.${state}`)} {tally[state]}
            </span>
          ))}
          <span className="opacity-70">
            {t('teacher.unset')} {students.length - draft.setCount}
          </span>
        </div>
      </div>

      <ul className="space-y-2">
        {students.map((student) => {
          const state = draft.draft[student.id];
          return (
            <li key={student.id} className={`card-soft p-3 ${draft.changed.has(student.id) ? 'row-dirty' : ''}`}>
              <div className="flex gap-3">
                <StudentPhoto name={student.name} photoUrl={student.photo_url} className="w-16 h-24" textClassName="text-2xl" />
                <div className="min-w-0 flex-1 space-y-2">
                  <div className="flex items-start justify-between gap-2">
                    <div className="min-w-0">
                      <p className="font-semibold leading-snug">{student.name}</p>
                      <p className="text-xs opacity-60 num">{student.student_id}</p>
                    </div>
                    {state && !editable && <StatusChip state={state} />}
                  </div>
                  {editable && (
                    <StateSegment
                      value={state}
                      onChange={(next) => draft.set(student.id, next)}
                      disabled={saving}
                      label={student.name}
                    />
                  )}
                </div>
              </div>
            </li>
          );
        })}
      </ul>

      {editable && (
        <SaveBar
          setCount={draft.setCount}
          total={students.length}
          hint={queued ? t('teacher.queued') : hint}
          canSave={draft.complete && draft.dirty}
          canUndo={draft.dirty}
          saving={saving}
          queued={queued}
          onSave={() => void onSave()}
          onUndo={draft.undo}
        />
      )}
      <LeaveGuard when={draft.dirty && !queued && !saving} />

      {toast && (
        <div className="toast toast-top toast-center z-[70]">
          <div role="status" className={`alert alert-${toast.kind} shadow-lg`}>
            <span>{toast.text}</span>
          </div>
        </div>
      )}
    </div>
  );
}

function SessionLink({ code, label, icon }: { code: string | null; label: string; icon: 'prev' | 'next' }) {
  const Icon = icon === 'prev' ? ChevronRight : ChevronLeft;
  if (!code) {
    return (
      <span className="btn btn-square join-item btn-disabled" aria-label={label} aria-disabled="true">
        <Icon className="size-5" strokeWidth={1.75} aria-hidden="true" />
      </span>
    );
  }
  return (
    <Link to={routes.teacher.roster(code)} replace className="btn btn-square join-item" aria-label={label}>
      <Icon className="size-5" strokeWidth={1.75} aria-hidden="true" />
    </Link>
  );
}
