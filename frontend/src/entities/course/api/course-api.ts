import { baseApi } from '@/shared/api';
import type { CourseDetail } from '../model/types';

export const courseApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** null when the student has no active course. */
    currentCourse: build.query<CourseDetail | null, void>({
      query: () => '/student/courses/current',
      transformResponse: (response: { data: CourseDetail | null }) => response.data,
      providesTags: ['Course'],
    }),
  }),
});

export const { useCurrentCourseQuery } = courseApi;
