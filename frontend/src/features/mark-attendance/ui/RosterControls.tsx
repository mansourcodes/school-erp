import { CheckCheck, CloudUpload, Save, Undo2, X } from 'lucide-react';
import { useEffect } from 'react';
import { useBlocker } from 'react-router';
import { ATTENDANCE_STATES } from '@/entities/attendance';
import type { RosterState } from '@/entities/class-session';
import { t } from '@/shared/i18n';
import { Dialog } from '@/shared/ui';

const ORDER: RosterState[] = ['present', 'late', 'late_excused', 'absent', 'absent_excused'];

/** Five-state segmented control: status colours, hatch = excused, 48 px targets (PLAN.md §8 roster). */
export function StateSegment({
  value,
  onChange,
  disabled,
  label,
}: {
  value: RosterState | null;
  onChange: (state: RosterState) => void;
  disabled?: boolean;
  label: string;
}) {
  return (
    <div className="seg" role="group" aria-label={label}>
      {ORDER.map((state) => {
        const meta = ATTENDANCE_STATES[state];
        return (
          <button
            key={state}
            type="button"
            className={`${meta.cls} ${meta.excused && value === state ? 'hatch' : ''}`}
            aria-pressed={value === state}
            aria-label={t(`attendanceState.${state}`)}
            disabled={disabled}
            onClick={() => {
              navigator.vibrate?.(10);
              onChange(state);
            }}
          >
            <b aria-hidden="true">{meta.code}</b>
            <span aria-hidden="true">{t(`attendanceState.${state}`)}</span>
          </button>
        );
      })}
    </div>
  );
}

export function QuickActions({ onAll, disabled }: { onAll: (state: RosterState) => void; disabled?: boolean }) {
  return (
    <div className="grid grid-cols-2 gap-2">
      <button
        type="button"
        className="btn btn-lg border-0 text-white disabled:opacity-40"
        style={{ background: 'var(--st-present)' }}
        disabled={disabled}
        onClick={() => onAll('present')}
      >
        <CheckCheck className="size-5" strokeWidth={1.75} aria-hidden="true" />
        {t('teacher.allPresent')}
      </button>
      <button
        type="button"
        className="btn btn-lg border-0 text-white disabled:opacity-40"
        style={{ background: 'var(--st-absent)' }}
        disabled={disabled}
        onClick={() => onAll('absent')}
      >
        <X className="size-5" strokeWidth={1.75} aria-hidden="true" />
        {t('teacher.allAbsent')}
      </button>
    </div>
  );
}

interface SaveBarProps {
  setCount: number;
  total: number;
  hint: string;
  canSave: boolean;
  canUndo: boolean;
  saving: boolean;
  queued: boolean;
  onSave: () => void;
  onUndo: () => void;
}

/** Sticky bottom bar of the roster task screen. */
export function SaveBar({ setCount, total, hint, canSave, canUndo, saving, queued, onSave, onUndo }: SaveBarProps) {
  return (
    <div className="fixed inset-x-0 bottom-0 z-40 bg-base-100/95 backdrop-blur border-t border-base-300 pb-[env(safe-area-inset-bottom)] lg:ps-72">
      <div className="max-w-6xl mx-auto px-4 lg:px-8 py-3 flex items-center gap-3">
        <div className="min-w-0 flex-1">
          <p className="text-sm font-semibold num">{t('teacher.setCount', { set: setCount, total })}</p>
          <p className="text-xs opacity-70 truncate flex items-center gap-1">
            {queued && (
              <span className="badge badge-warning badge-xs gap-1">
                <CloudUpload className="size-3" strokeWidth={2} aria-hidden="true" />
                {t('teacher.pendingSync')}
              </span>
            )}
            {hint}
          </p>
        </div>
        <button type="button" className="btn btn-ghost btn-square" aria-label={t('teacher.undo')} disabled={!canUndo || saving} onClick={onUndo}>
          <Undo2 className="size-5" strokeWidth={1.75} aria-hidden="true" />
        </button>
        <button type="button" className="btn btn-primary btn-lg" disabled={!canSave || saving} onClick={onSave}>
          {saving ? (
            <span className="loading loading-spinner loading-sm" aria-hidden="true" />
          ) : (
            <Save className="size-5" strokeWidth={1.75} aria-hidden="true" />
          )}
          {t('teacher.save')}
        </button>
      </div>
    </div>
  );
}

/** "لديك تغييرات غير محفوظة": in-app navigation asks first; closing the tab gets the browser prompt. */
export function LeaveGuard({ when }: { when: boolean }) {
  const blocker = useBlocker(({ currentLocation, nextLocation }) => when && currentLocation.pathname !== nextLocation.pathname);

  useEffect(() => {
    if (!when) return;
    const warn = (event: BeforeUnloadEvent) => event.preventDefault();
    window.addEventListener('beforeunload', warn);
    return () => window.removeEventListener('beforeunload', warn);
  }, [when]);

  return (
    <Dialog
      open={blocker.state === 'blocked'}
      onClose={() => blocker.reset?.()}
      labelledBy="leave-title"
      closeLabel={t('teacher.stay')}
    >
      <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
        <div className="sheet-grip" />
        <h2 id="leave-title" className="font-display text-lg">
          {t('teacher.leaveTitle')}
        </h2>
        <p className="text-sm opacity-75 mt-1">{t('teacher.leaveBody')}</p>
        <div className="grid grid-cols-2 gap-2 mt-6">
          <button type="button" className="btn btn-lg" onClick={() => blocker.reset?.()}>
            {t('teacher.stay')}
          </button>
          <button type="button" className="btn btn-error btn-lg" onClick={() => blocker.proceed?.()}>
            {t('teacher.leave')}
          </button>
        </div>
      </div>
    </Dialog>
  );
}
