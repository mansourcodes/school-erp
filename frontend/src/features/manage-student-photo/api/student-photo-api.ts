import { baseApi } from '@/shared/api';

interface PhotoTarget {
  classroomId: number;
  studentId: number;
}

/** Teacher sets / removes a student's photo; applies at once (PLAN.md §8, Q11). */
export const studentPhotoApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    uploadStudentPhoto: build.mutation<{ photo_url: string }, PhotoTarget & { photo: Blob }>({
      query: ({ classroomId, studentId, photo }) => {
        const body = new FormData();
        body.append('photo', photo, 'photo.jpg');
        return { url: `/teacher/classrooms/${classroomId}/students/${studentId}/photo`, method: 'POST', body };
      },
      transformResponse: (response: { data: { photo_url: string } }) => response.data,
      invalidatesTags: ['Classroom', 'ClassSession', 'Stats'],
    }),
    deleteStudentPhoto: build.mutation<void, PhotoTarget>({
      query: ({ classroomId, studentId }) => ({
        url: `/teacher/classrooms/${classroomId}/students/${studentId}/photo`,
        method: 'DELETE',
      }),
      invalidatesTags: ['Classroom', 'ClassSession', 'Stats'],
    }),
  }),
});

export const { useUploadStudentPhotoMutation, useDeleteStudentPhotoMutation } = studentPhotoApi;
