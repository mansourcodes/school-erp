import { baseApi } from '@/shared/api';

/** POST /student/messages/{id}/read (idempotent); refreshes the inbox and the /me badge. */
export const readMessageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    markMessageRead: build.mutation<{ read_at: string }, number>({
      query: (id) => ({ url: `/student/messages/${id}/read`, method: 'POST' }),
      transformResponse: (response: { data: { read_at: string } }) => response.data,
      invalidatesTags: ['Inbox', 'Viewer'],
    }),
  }),
});

export const { useMarkMessageReadMutation } = readMessageApi;
