import { useEffect } from 'react';
import { useAppDispatch } from '@/shared/lib';
import { refreshOutbox } from './outbox-runner';

/**
 * Mounted once in the teacher's shell: picks up rows left from a closed app or from an offline
 * save the service worker replayed, and resumes when the connection returns.
 */
export function useOutboxRunner(enabled: boolean) {
  const dispatch = useAppDispatch();

  useEffect(() => {
    if (!enabled) return;
    void dispatch(refreshOutbox());

    const onOnline = () => void dispatch(refreshOutbox());
    const onMessage = (event: MessageEvent) => {
      // A queued roster reached the server: its first save may have recorded pushes.
      if ((event.data as { type?: string } | undefined)?.type === 'ROSTER_SYNCED') void dispatch(refreshOutbox());
    };
    window.addEventListener('online', onOnline);
    navigator.serviceWorker?.addEventListener('message', onMessage);
    return () => {
      window.removeEventListener('online', onOnline);
      navigator.serviceWorker?.removeEventListener('message', onMessage);
    };
  }, [enabled, dispatch]);
}
