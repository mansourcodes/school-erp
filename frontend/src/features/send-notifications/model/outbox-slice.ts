import { createSlice, type PayloadAction } from '@reduxjs/toolkit';
import { sessionEnded, sessionExpired } from '@/shared/api';
import type { DeliveryStatus, DeliveryType, PendingDelivery } from '../api/push-deliveries-api';

export interface OutboxItem {
  id: number;
  type: DeliveryType;
  studentName: string | null;
  status: DeliveryStatus;
}

interface OutboxState {
  items: OutboxItem[];
  /** Waiting for the connection (offline) or for a rate limit to pass. */
  paused: 'offline' | 'rate-limited' | null;
}

const initialState: OutboxState = { items: [], paused: null };

const outboxSlice = createSlice({
  name: 'pushOutbox',
  initialState,
  reducers: {
    /** Rows from the server: new ones join as pending; a failed row offered again is retried. */
    deliveriesLoaded(state, action: PayloadAction<PendingDelivery[]>) {
      for (const row of action.payload) {
        const item = state.items.find((i) => i.id === row.id);
        if (!item) state.items.push({ id: row.id, type: row.type, studentName: row.student_name, status: 'pending' });
        else if (item.status === 'failed') item.status = 'pending';
      }
    },
    deliveryStatusChanged(state, action: PayloadAction<{ id: number; status: DeliveryStatus }>) {
      const item = state.items.find((i) => i.id === action.payload.id);
      if (item) item.status = action.payload.status;
    },
    outboxPaused(state, action: PayloadAction<OutboxState['paused']>) {
      state.paused = action.payload;
    },
    /** Forget finished rows (the progress page starts fresh next time). */
    outboxCleared(state) {
      state.items = state.items.filter((i) => i.status === 'pending' || i.status === 'sending');
    },
  },
  extraReducers: (builder) => {
    // Another user may sign in on this device next.
    builder.addCase(sessionEnded, () => initialState).addCase(sessionExpired, () => initialState);
  },
});

export const { deliveriesLoaded, deliveryStatusChanged, outboxPaused, outboxCleared } = outboxSlice.actions;
export const pushOutboxReducer = outboxSlice.reducer;

export const selectOutbox = (state: RootState) => state.pushOutbox;

export interface OutboxProgress {
  total: number;
  /** Finished one way or another (sent, failed, skipped). */
  done: number;
  sent: number;
  failed: number;
  active: boolean;
}

export function progressOf(items: OutboxItem[]): OutboxProgress {
  const count = (...s: DeliveryStatus[]) => items.filter((i) => s.includes(i.status)).length;
  const active = count('pending', 'sending');
  return { total: items.length, done: items.length - active, sent: count('sent'), failed: count('failed'), active: active > 0 };
}
