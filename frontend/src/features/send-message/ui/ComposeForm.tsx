import { Info, Send } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useTeacherClassroomQuery, useTeacherClassroomsQuery } from '@/entities/classroom';
import { MESSAGE_MAX } from '@/entities/message';
import { StudentPhoto } from '@/entities/student';
import { t } from '@/shared/i18n';
import { LeaveGuard, LoadingBlocks } from '@/shared/ui';
import { type SendMessageResult, useSendMessageMutation } from '../api/send-message-api';

interface ComposeFormProps {
  /** From "مراسلة طلاب الصف" (?room=). */
  initialClassroomId?: number;
  onSent: (result: SendMessageResult) => void;
}

/** New message (PLAN.md §8 screen 8): ① classroom ② students ③ text, then send. */
export function ComposeForm({ initialClassroomId, onSent }: ComposeFormProps) {
  const classrooms = useTeacherClassroomsQuery();
  const [classroomId, setClassroomId] = useState<number | undefined>(initialClassroomId);
  const effectiveId = classroomId ?? classrooms.data?.[0]?.id;
  const classroom = useTeacherClassroomQuery(effectiveId ?? 0, { skip: !effectiveId });
  const students = classroom.data?.students ?? [];

  const [selected, setSelected] = useState<Set<number>>(new Set());
  const [body, setBody] = useState('');
  const [send, { isLoading: sending }] = useSendMessageMutation();
  const [error, setError] = useState<string | null>(null);
  const [sent, setSent] = useState<SendMessageResult | null>(null);

  const all = students.length > 0 && selected.size === students.length;
  const canSend = selected.size > 0 && body.trim().length > 0 && body.length <= MESSAGE_MAX && !sending;

  const pickClassroom = (id: number) => {
    setClassroomId(id);
    setSelected(new Set());
  };
  const toggle = (id: number) => {
    const next = new Set(selected);
    if (next.has(id)) next.delete(id);
    else next.add(id);
    setSelected(next);
  };

  const submit = async () => {
    if (!effectiveId) return;
    setError(null);
    try {
      const result = await send({ classroom_id: effectiveId, student_ids: [...selected], body: body.trim() }).unwrap();
      // Navigate after the guard has re-rendered without unsent text (see effect below).
      setSent(result);
    } catch {
      setError(t('messages.failed'));
    }
  };

  useEffect(() => {
    if (sent) onSent(sent);
  }, [sent, onSent]);

  if (classrooms.isLoading) return <LoadingBlocks />;

  return (
    <div className="space-y-4 pb-24">
      <section className="space-y-2">
        <h2 className="font-display text-base">① {t('messages.stepClass')}</h2>
        <div className="rail" role="group" aria-label={t('messages.stepClass')}>
          {classrooms.data?.map((c) => (
            <button key={c.id} type="button" className="chip" aria-pressed={c.id === effectiveId} onClick={() => pickClassroom(c.id)}>
              {t('teacher.room', { room: c.number ?? '' })}
            </button>
          ))}
        </div>
      </section>

      <section className="card-soft p-4 space-y-2">
        <div className="flex items-center justify-between gap-2">
          <h2 className="font-display text-base">② {t('messages.stepStudents')}</h2>
          <span className="text-sm opacity-70 num">{t('messages.selected', { n: selected.size, total: students.length })}</span>
        </div>
        <button
          type="button"
          className="btn btn-sm btn-ghost text-primary"
          onClick={() => setSelected(all ? new Set() : new Set(students.map((s) => s.id)))}
          disabled={students.length === 0}
        >
          {all ? t('messages.clearAll') : t('messages.selectAll')}
        </button>
        {classroom.isFetching && !classroom.data ? (
          <LoadingBlocks heights={['h-14', 'h-14']} />
        ) : (
          <ul className="divide-y divide-base-300 -mx-2">
            {students.map((s) => (
              <li key={s.id}>
                <label className="min-h-14 px-2 py-2 flex items-center gap-3 cursor-pointer">
                  <input type="checkbox" className="checkbox checkbox-primary" checked={selected.has(s.id)} onChange={() => toggle(s.id)} />
                  <StudentPhoto name={s.name} photoUrl={s.photo_url} className="w-8 h-10" />
                  <span className="min-w-0 flex-1">
                    <span className="block truncate">{s.name}</span>
                    <span className="block text-xs opacity-60 num">{s.student_id}</span>
                  </span>
                </label>
              </li>
            ))}
          </ul>
        )}
      </section>

      <section className="card-soft p-4 space-y-2">
        <label htmlFor="message-body" className="font-display text-base block">
          ③ {t('messages.stepText')}
        </label>
        <textarea
          id="message-body"
          className="textarea w-full min-h-36"
          maxLength={MESSAGE_MAX}
          placeholder={t('messages.placeholder')}
          value={body}
          onChange={(e) => setBody(e.target.value)}
        />
        <div className="flex items-center justify-between text-xs opacity-70">
          <span className="inline-flex items-center gap-1">
            <Info className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
            {t('messages.teacherNote')}
          </span>
          <span className="num" aria-live="polite">
            {t('messages.counter', { n: body.length, max: MESSAGE_MAX })}
          </span>
        </div>
        {error && (
          <p role="alert" className="text-sm text-error">
            {error}
          </p>
        )}
      </section>

      <div className="fixed inset-x-0 bottom-0 z-40 bg-base-100/95 backdrop-blur border-t border-base-300 pb-[env(safe-area-inset-bottom)] lg:ps-72">
        <div className="max-w-6xl mx-auto px-4 lg:px-8 py-3">
          <button type="button" className="btn btn-primary btn-lg btn-block" disabled={!canSend} onClick={() => void submit()}>
            {sending ? (
              <span className="loading loading-spinner loading-sm" aria-hidden="true" />
            ) : (
              <Send className="size-5" strokeWidth={1.75} aria-hidden="true" />
            )}
            {all ? t('messages.sendAll') : t('messages.sendTo', { n: selected.size })}
          </button>
        </div>
      </div>

      <LeaveGuard
        when={!sent && body.trim().length > 0}
        title={t('messages.leaveTitle')}
        body={t('messages.leaveBody')}
        stayLabel={t('teacher.stay')}
        leaveLabel={t('teacher.leave')}
      />
    </div>
  );
}
