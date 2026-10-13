import { baseApi } from '@/shared/api';

export interface SendMessageArgs {
  classroom_id: number;
  student_ids: number[];
  body: string;
}

/** POST /teacher/messages (PLAN.md §8): my current classroom, its students only, body 1–500. */
export const sendMessageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    sendMessage: build.mutation<{ id: number; recipients_count: number }, SendMessageArgs>({
      query: (body) => ({ url: '/teacher/messages', method: 'POST', body }),
      transformResponse: (response: { data: { id: number; recipients_count: number } }) => response.data,
      invalidatesTags: ['Sent'],
    }),
  }),
});

export const { useSendMessageMutation } = sendMessageApi;
