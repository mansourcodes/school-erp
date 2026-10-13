import { CircleCheck } from 'lucide-react';
import { useEffect } from 'react';
import { useLocation, useNavigate } from 'react-router';
import { OutboxProgress, outboxCleared, refreshOutbox } from '@/features/send-notifications';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { useAppDispatch } from '@/shared/lib';

export interface NotifyPageState {
  /** What was just saved, for the confirmation line. */
  saved: 'attendance' | 'message';
  /** Where "متابعة" goes. */
  next: string;
}

/**
 * /teacher/notify: shown right after a roster save or a message. The save is already done; this
 * page sends the students' notifications one by one (push outbox, PLAN.md §5.12). Leaving does
 * not stop the sending while the app stays open.
 */
export function TeacherNotifyPage() {
  const dispatch = useAppDispatch();
  const navigate = useNavigate();
  const state = useLocation().state as NotifyPageState | null;

  useEffect(() => {
    // Start from this save's rows only, then load and send them.
    dispatch(outboxCleared());
    void dispatch(refreshOutbox());
  }, [dispatch]);

  return (
    <div className="space-y-4 max-w-xl">
      <div role="status" className="alert alert-soft alert-success">
        <CircleCheck className="size-5" strokeWidth={1.75} aria-hidden="true" />
        <span>{state?.saved === 'message' ? t('notify.savedMessage') : t('notify.savedAttendance')}</span>
      </div>

      <section className="card-soft p-4 space-y-3">
        <h2 className="font-display text-lg">{t('notify.heading')}</h2>
        <p className="text-sm opacity-75">{t('notify.background')}</p>
        <OutboxProgress />
      </section>

      <button type="button" className="btn btn-primary w-full sm:w-auto" onClick={() => navigate(state?.next ?? routes.teacher.today, { replace: true })}>
        {t('notify.continue')}
      </button>
    </div>
  );
}
