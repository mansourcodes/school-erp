import { baseApi } from '@/shared/api';
import type { CourseDetail, CourseSummary } from '../model/types';

export const courseApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    /** null when the student has no active course. */
    currentCourse: build.query<CourseDetail | null, void>({
      query: () => '/student/courses/current',
      transformResponse: (response: { data: CourseDetail | null }) => response.data,
      providesTags: ['Course'],
    }),
    /** Every enrollment except the current one, newest first. */
    archivedCourses: build.query<CourseSummary[], void>({
      query: () => '/student/courses?status=archived',
      transformResponse: (response: { data: CourseSummary[] }) => response.data,
      providesTags: ['Course'],
    }),
    /** One of the student's courses; `status: 'archived'` = read-only. */
    course: build.query<CourseDetail, number>({
      query: (id) => `/student/courses/${id}`,
      transformResponse: (response: { data: CourseDetail }) => response.data,
      providesTags: (_result, _error, id) => [{ type: 'Course', id }],
    }),
  }),
});

export const { useCurrentCourseQuery, useArchivedCoursesQuery, useCourseQuery } = courseApi;
