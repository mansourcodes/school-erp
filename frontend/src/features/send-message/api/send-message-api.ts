import { baseApi } from '@/shared/api';

export interface SendMessageArgs {
  classroom_id: number;
  student_ids: number[];
  body: string;
}

/** `push_pending`: students' notifications the app now sends one by one (push outbox). */
export interface SendMessageResult {
  id: number;
  recipients_count: number;
  push_pending: number;
}

/** POST /teacher/messages (PLAN.md §8): my current classroom, its students only, body 1–500. */
export const sendMessageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    sendMessage: build.mutation<SendMessageResult, SendMessageArgs>({
      query: (body) => ({ url: '/teacher/messages', method: 'POST', body }),
      transformResponse: (response: { data: SendMessageResult }) => response.data,
      invalidatesTags: ['Sent'],
    }),
  }),
});

export const { useSendMessageMutation } = sendMessageApi;
