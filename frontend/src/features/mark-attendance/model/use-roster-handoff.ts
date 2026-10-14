import { useEffect } from 'react';
import type { RosterState } from '@/entities/class-session';
import { useAppDispatch } from '@/shared/lib';
import { saveAttendanceApi } from '../api/save-attendance';
import { markSynced } from '../lib/offline-queue';

/**
 * An offline save the service worker could not replay (its CSRF header went stale while the
 * session was renewed) is handed to the page, which sends it with a fresh token.
 * Mounted once in the teacher's shell; `onSaved` runs after the save reached the server.
 */
export function useRosterHandoff(enabled: boolean, onSaved: () => void) {
  const dispatch = useAppDispatch();

  useEffect(() => {
    if (!enabled) return;
    const onMessage = (event: MessageEvent) => {
      const data = event.data as { type?: string; code?: string; states?: Record<number, RosterState> } | undefined;
      if (data?.type !== 'ROSTER_REPLAY' || !data.code || !data.states) return;
      const code = data.code;
      void dispatch(saveAttendanceApi.endpoints.saveAttendance.initiate({ code, states: data.states }))
        .unwrap()
        .then(onSaved, () => {
          // Refused (e.g. the roster changed): like ROSTER_REJECTED, it no longer waits on this device.
        })
        .finally(() => markSynced(code));
    };
    navigator.serviceWorker?.addEventListener('message', onMessage);
    return () => navigator.serviceWorker?.removeEventListener('message', onMessage);
  }, [enabled, onSaved, dispatch]);
}
