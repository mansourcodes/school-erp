import { baseApi } from '@/shared/api';

export type DeliveryType = 'attendance' | 'messages';

export interface PendingDelivery {
  id: number;
  type: DeliveryType;
  student_name: string | null;
}

export type DeliveryStatus = 'pending' | 'sending' | 'sent' | 'failed' | 'skipped';

/**
 * The teacher's push outbox (PLAN.md §5.12): a save only records the pushes; the app sends them
 * one request at a time, so the save answers at once.
 */
export const pushDeliveriesApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    pendingDeliveries: build.query<PendingDelivery[], void>({
      query: () => '/teacher/push-deliveries',
      transformResponse: (response: { data: PendingDelivery[] }) => response.data,
      keepUnusedDataFor: 0,
    }),
    sendDelivery: build.mutation<{ id: number; status: DeliveryStatus }, number>({
      query: (id) => ({ url: `/teacher/push-deliveries/${id}/send`, method: 'POST' }),
      transformResponse: (response: { data: { id: number; status: DeliveryStatus } }) => response.data,
    }),
  }),
});
