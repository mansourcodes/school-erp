import { useEffect, useState } from 'react';
import { clearRosterQueue, ROSTER_QUEUE_EVENT, ROSTER_QUEUE_KEY } from '@/shared/lib';

/**
 * Teacher saves that failed offline are queued by the service worker (Background Sync) and
 * replayed when the connection returns (PLAN.md §4, Q8). The page only tracks which session
 * codes are waiting, so the save bar can show "بانتظار الإرسال" until the worker confirms.
 */
const KEY = ROSTER_QUEUE_KEY;
const EVENT = ROSTER_QUEUE_EVENT;

function read(): string[] {
  try {
    return JSON.parse(localStorage.getItem(KEY) ?? '[]') as string[];
  } catch {
    return [];
  }
}

function write(codes: string[]) {
  try {
    localStorage.setItem(KEY, JSON.stringify(codes));
  } catch {
    // Private mode: the badge lasts for this page only.
  }
  window.dispatchEvent(new Event(EVENT));
}

export function markQueued(code: string) {
  write([...new Set([...read(), code])]);
}

export function markSynced(code: string) {
  write(read().filter((c) => c !== code));
}

export const clearQueued = clearRosterQueue;

/** True while this session's save is waiting in the worker's queue. */
export function useQueued(code: string): boolean {
  const [queued, setQueued] = useState(() => read().includes(code));

  useEffect(() => {
    const refresh = () => setQueued(read().includes(code));
    const onMessage = (event: MessageEvent) => {
      const data = event.data as { type?: string; code?: string } | undefined;
      // Sent or refused by the server: either way it is no longer waiting on this device.
      if ((data?.type === 'ROSTER_SYNCED' || data?.type === 'ROSTER_REJECTED') && data.code) markSynced(data.code);
    };
    refresh();
    window.addEventListener(EVENT, refresh);
    navigator.serviceWorker?.addEventListener('message', onMessage);
    return () => {
      window.removeEventListener(EVENT, refresh);
      navigator.serviceWorker?.removeEventListener('message', onMessage);
    };
  }, [code]);

  return queued;
}

/** A queue only exists where a service worker controls the page (installed / visited PWA). */
export const canQueue = () => !!navigator.serviceWorker?.controller;
