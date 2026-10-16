import { useEffect } from 'react';
import type { RosterState } from '@/entities/class-session';
import { useAppDispatch } from '@/shared/lib';
import { saveAttendanceApi } from '../api/save-attendance';
import { markSynced } from '../lib/offline-queue';

/** A refusal that will not change on a retry (e.g. the roster changed). Offline, 5xx, 429, or signed out: retry later. */
function isFinalRefusal(error: unknown) {
  const status = (error as { status?: unknown } | undefined)?.status;
  return typeof status === 'number' && status >= 400 && status < 500 && ![401, 419, 429].includes(status);
}

/**
 * An offline save the service worker could not replay (its CSRF header went stale while the
 * session was renewed) is handed to the page, which sends it with a fresh token. The worker keeps it
 * until the page confirms (ROSTER_REPLAY_DONE), so a save is never dropped on the way.
 * Mounted once in the teacher's shell, which asks the worker for waiting saves when it opens;
 * `onSaved` runs after the save reached the server.
 */
export function useRosterHandoff(enabled: boolean, onSaved: () => void) {
  const dispatch = useAppDispatch();

  useEffect(() => {
    if (!enabled) return;
    const onMessage = (event: MessageEvent) => {
      const data = event.data as { type?: string; code?: string; states?: Record<number, RosterState> } | undefined;
      if (data?.type !== 'ROSTER_REPLAY' || !data.code || !data.states) return;
      const code = data.code;
      const worker = (event.source as ServiceWorker | null) ?? navigator.serviceWorker?.controller;
      const done = () => {
        worker?.postMessage({ type: 'ROSTER_REPLAY_DONE', code });
        markSynced(code);
      };
      void dispatch(saveAttendanceApi.endpoints.saveAttendance.initiate({ code, states: data.states }))
        .unwrap()
        .then(
          () => {
            done();
            onSaved();
          },
          (error: unknown) => {
            // Refused like ROSTER_REJECTED: it no longer waits on this device. Otherwise it stays queued.
            if (isFinalRefusal(error)) done();
          },
        );
    };
    navigator.serviceWorker?.addEventListener('message', onMessage);
    navigator.serviceWorker?.controller?.postMessage({ type: 'ROSTER_FLUSH' });
    return () => navigator.serviceWorker?.removeEventListener('message', onMessage);
  }, [enabled, onSaved, dispatch]);
}
