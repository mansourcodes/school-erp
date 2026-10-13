/**
 * Device-side bookkeeping of teacher saves waiting in the service worker's queue
 * (PLAN.md §4 "saving attendance with a weak connection"). Shared so logout can clear it.
 */
export const ROSTER_QUEUE_KEY = 'gh-roster-queued';
export const ROSTER_QUEUE_EVENT = 'gh-roster-queue';

/** Drops queued saves on this device (logout on a shared phone). */
export function clearRosterQueue() {
  try {
    localStorage.removeItem(ROSTER_QUEUE_KEY);
  } catch {
    // Private mode.
  }
  window.dispatchEvent(new Event(ROSTER_QUEUE_EVENT));
  navigator.serviceWorker?.controller?.postMessage({ type: 'CLEAR_ROSTER_QUEUE' });
}
