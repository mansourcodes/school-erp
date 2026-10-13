import { baseApi } from '@/shared/api';

/** DELETE /teacher/messages/{id}: own messages only; gone from every inbox at once. */
export const deleteMessageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    deleteMessage: build.mutation<void, number>({
      query: (id) => ({ url: `/teacher/messages/${id}`, method: 'DELETE' }),
      invalidatesTags: ['Sent'],
    }),
  }),
});

export const { useDeleteMessageMutation } = deleteMessageApi;
