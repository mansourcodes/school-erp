import { useEffect } from 'react';
import { useBlocker } from 'react-router';
import { Dialog } from './Dialog';

interface LeaveGuardProps {
  when: boolean;
  title: string;
  body: string;
  stayLabel: string;
  leaveLabel: string;
}

/** Unsaved work: in-app navigation asks first in a sheet; closing the tab gets the browser prompt. */
export function LeaveGuard({ when, title, body, stayLabel, leaveLabel }: LeaveGuardProps) {
  const blocker = useBlocker(({ currentLocation, nextLocation }) => when && currentLocation.pathname !== nextLocation.pathname);

  useEffect(() => {
    if (!when) return;
    const warn = (event: BeforeUnloadEvent) => event.preventDefault();
    window.addEventListener('beforeunload', warn);
    return () => window.removeEventListener('beforeunload', warn);
  }, [when]);

  return (
    <Dialog open={blocker.state === 'blocked'} onClose={() => blocker.reset?.()} labelledBy="leave-guard-title" closeLabel={stayLabel}>
      <div className="p-6 pb-[calc(1.5rem+env(safe-area-inset-bottom))]">
        <div className="sheet-grip" />
        <h2 id="leave-guard-title" className="font-display text-lg">
          {title}
        </h2>
        <p className="text-sm opacity-75 mt-1">{body}</p>
        <div className="grid grid-cols-2 gap-2 mt-6">
          <button type="button" className="btn btn-lg" onClick={() => blocker.reset?.()}>
            {stayLabel}
          </button>
          <button type="button" className="btn btn-error btn-lg" onClick={() => blocker.proceed?.()}>
            {leaveLabel}
          </button>
        </div>
      </div>
    </Dialog>
  );
}
