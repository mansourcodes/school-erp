import { type TeacherViewer, viewerApi } from '@/entities/viewer';
import { baseApi } from '@/shared/api';

/** The teacher's own sign-in username; the answer is the updated viewer, written straight into /me. */
export const changeUsernameApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    changeTeacherUsername: build.mutation<TeacherViewer, { username: string; current_password: string }>({
      query: (body) => ({ url: '/teacher/username', method: 'PUT', body }),
      transformResponse: (response: { data: TeacherViewer }) => response.data,
      async onQueryStarted(_arg, { dispatch, queryFulfilled }) {
        try {
          const { data } = await queryFulfilled;
          dispatch(viewerApi.util.upsertQueryData('me', undefined, data));
        } catch {
          // Handled by the form.
        }
      },
    }),
  }),
});

export const { useChangeTeacherUsernameMutation } = changeUsernameApi;
