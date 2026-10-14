import { baseApi } from '@/shared/api';

/** The teacher's own photo; their students see it next to their name. Refreshes /me. */
export const teacherPhotoApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    uploadTeacherPhoto: build.mutation<{ photo_url: string }, Blob>({
      query: (photo) => {
        const body = new FormData();
        body.append('photo', photo, 'photo.jpg');
        return { url: '/teacher/photo', method: 'POST', body };
      },
      transformResponse: (response: { data: { photo_url: string } }) => response.data,
      invalidatesTags: ['Viewer'],
    }),
    deleteTeacherPhoto: build.mutation<void, void>({
      query: () => ({ url: '/teacher/photo', method: 'DELETE' }),
      invalidatesTags: ['Viewer'],
    }),
  }),
});

export const { useUploadTeacherPhotoMutation, useDeleteTeacherPhotoMutation } = teacherPhotoApi;
