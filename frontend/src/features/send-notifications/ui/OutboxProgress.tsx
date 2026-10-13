import { Check, Clock, LoaderCircle, Minus, RotateCw, WifiOff, X } from 'lucide-react';
import { t } from '@/shared/i18n';
import { useAppDispatch, useAppSelector } from '@/shared/lib';
import type { DeliveryStatus } from '../api/push-deliveries-api';
import { refreshOutbox } from '../model/outbox-runner';
import { progressOf, selectOutbox } from '../model/outbox-slice';

const ICON: Record<DeliveryStatus, { icon: typeof Check; cls: string; spin?: boolean }> = {
  pending: { icon: Clock, cls: 'opacity-50' },
  sending: { icon: LoaderCircle, cls: 'text-primary', spin: true },
  sent: { icon: Check, cls: 'text-success' },
  failed: { icon: X, cls: 'text-error' },
  skipped: { icon: Minus, cls: 'opacity-50' },
};

/** Progress of the push outbox: count, bar, one row per student, retry for failed ones. */
export function OutboxProgress() {
  const dispatch = useAppDispatch();
  const { items, paused } = useAppSelector(selectOutbox);
  const progress = progressOf(items);

  if (items.length === 0) {
    return <p className="text-sm opacity-70">{t('notify.nothing')}</p>;
  }

  return (
    <div className="space-y-3">
      <div aria-live="polite">
        <div className="flex items-baseline justify-between gap-2 text-sm">
          <span className="font-medium">
            {progress.active ? t('notify.sending') : progress.failed ? t('notify.doneWithFailures') : t('notify.done')}
          </span>
          <span className="num opacity-70">
            {progress.done} / {progress.total}
          </span>
        </div>
        <progress className="progress progress-primary w-full mt-1.5" value={progress.done} max={progress.total} />
        {paused === 'offline' && (
          <p className="text-xs text-warning mt-1.5 flex items-center gap-1.5">
            <WifiOff className="size-3.5" strokeWidth={1.75} aria-hidden="true" />
            {t('notify.pausedOffline')}
          </p>
        )}
        {paused === 'rate-limited' && <p className="text-xs opacity-70 mt-1.5">{t('notify.pausedBusy')}</p>}
      </div>

      <ul className="card-soft divide-y divide-base-300">
        {items.map((item) => {
          const { icon: Icon, cls, spin } = ICON[item.status];
          return (
            <li key={item.id} className="flex items-center gap-3 px-4 py-2.5 text-sm">
              <Icon className={`size-4 shrink-0 ${cls} ${spin ? 'animate-spin' : ''}`} strokeWidth={2} aria-hidden="true" />
              <span className="flex-1 min-w-0 truncate">{item.studentName ?? '—'}</span>
              <span className="text-xs opacity-60">{t(`notify.type.${item.type}`)}</span>
              <span className="sr-only">{t(`notify.status.${item.status}`)}</span>
            </li>
          );
        })}
      </ul>

      {!progress.active && progress.failed > 0 && (
        <button type="button" className="btn btn-outline btn-sm gap-1.5" onClick={() => void dispatch(refreshOutbox())}>
          <RotateCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
          {t('notify.retry')}
        </button>
      )}
    </div>
  );
}
