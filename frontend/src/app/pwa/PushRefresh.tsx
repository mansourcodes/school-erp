import { useEffect } from 'react';
import { baseApi } from '@/shared/api';
import { useAppDispatch } from '@/shared/lib';

/** A push arrived while the app is open: refresh the unread badge, inbox and attendance (PLAN.md §7.2). */
export function PushRefresh() {
  const dispatch = useAppDispatch();

  useEffect(() => {
    const onMessage = (event: MessageEvent) => {
      if ((event.data as { type?: string } | undefined)?.type === 'PUSH_RECEIVED') {
        dispatch(baseApi.util.invalidateTags(['Viewer', 'Inbox', 'Attendance', 'Stats']));
      }
    };
    navigator.serviceWorker?.addEventListener('message', onMessage);
    return () => navigator.serviceWorker?.removeEventListener('message', onMessage);
  }, [dispatch]);

  return null;
}
