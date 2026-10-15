import { baseApi } from '@/shared/api';

export interface EditMessageArgs {
  id: number;
  body: string;
}

/** PATCH /teacher/messages/{id}: own messages only; the text changes in every inbox, no new push. */
export const editMessageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    editMessage: build.mutation<void, EditMessageArgs>({
      query: ({ id, body }) => ({ url: `/teacher/messages/${id}`, method: 'PATCH', body: { body } }),
      invalidatesTags: ['Sent'],
    }),
  }),
});

export const { useEditMessageMutation } = editMessageApi;
