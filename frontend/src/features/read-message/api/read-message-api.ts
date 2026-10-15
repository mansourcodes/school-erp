import { baseApi } from '@/shared/api';

/**
 * POST /student/messages/{id}/read (idempotent); refreshes the inbox and the /me badge.
 * `read_at` stays null while an admin views as the student (the message stays unread for them).
 */
export const readMessageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    markMessageRead: build.mutation<{ read_at: string | null }, number>({
      query: (id) => ({ url: `/student/messages/${id}/read`, method: 'POST' }),
      transformResponse: (response: { data: { read_at: string | null } }) => response.data,
      invalidatesTags: ['Inbox', 'Viewer'],
    }),
  }),
});

export const { useMarkMessageReadMutation } = readMessageApi;
