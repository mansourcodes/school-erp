import { toApiError } from '@/shared/api';
import { pushDeliveriesApi } from '../api/push-deliveries-api';
import { deliveriesLoaded, deliveryStatusChanged, outboxPaused } from './outbox-slice';

/** One loop per app: a second trigger while it runs only adds rows for it to pick up. */
let running = false;

const wait = (ms: number) => new Promise((resolve) => window.setTimeout(resolve, ms));

/** Loads the teacher's unsent rows (after a save, on start, back online) and sends them. */
export const refreshOutbox = () => async (dispatch: AppDispatch) => {
  try {
    const rows = await dispatch(
      pushDeliveriesApi.endpoints.pendingDeliveries.initiate(undefined, { forceRefetch: true, subscribe: false }),
    ).unwrap();
    dispatch(deliveriesLoaded(rows));
  } catch {
    // Offline or signed out: the next trigger loads them again.
  }
  await dispatch(runOutbox());
};

/** Sends pending rows one request at a time until none is left. */
export const runOutbox = () => async (dispatch: AppDispatch, getState: () => RootState) => {
  if (running) return;
  running = true;
  dispatch(outboxPaused(null));
  try {
    for (;;) {
      const next = getState().pushOutbox.items.find((i) => i.status === 'pending');
      if (!next) break;

      dispatch(deliveryStatusChanged({ id: next.id, status: 'sending' }));
      const request = dispatch(pushDeliveriesApi.endpoints.sendDelivery.initiate(next.id));
      try {
        const { status } = await request.unwrap();
        // "sending" here means another tab or device is sending it right now.
        dispatch(deliveryStatusChanged({ id: next.id, status: status === 'sending' ? 'skipped' : status }));
      } catch (error) {
        const apiError = toApiError(error);
        if (apiError.status === 'offline') {
          // Resumes on the `online` event (useOutboxRunner).
          dispatch(deliveryStatusChanged({ id: next.id, status: 'pending' }));
          dispatch(outboxPaused('offline'));
          break;
        }
        if (apiError.status === 429) {
          dispatch(deliveryStatusChanged({ id: next.id, status: 'pending' }));
          dispatch(outboxPaused('rate-limited'));
          await wait((apiError.retryAfter ?? 10) * 1000);
          dispatch(outboxPaused(null));
          continue;
        }
        if (apiError.status === 401 || apiError.status === 419) break; // signed out: the session reset clears the list
        // 404: not mine or already gone; anything else: failed (the server allows a few retries).
        dispatch(deliveryStatusChanged({ id: next.id, status: apiError.status === 404 ? 'skipped' : 'failed' }));
      } finally {
        request.reset();
      }
    }
  } finally {
    running = false;
  }
};
