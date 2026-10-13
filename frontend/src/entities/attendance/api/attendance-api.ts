import { baseApi } from '@/shared/api';
import type { AttendanceStats, Session } from '../model/types';

/** `courseId` omitted = the current course. */
export const attendanceApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    attendance: build.query<Session[], number | undefined>({
      query: (courseId) => ({ url: '/student/attendance', params: courseId ? { course_id: courseId } : undefined }),
      transformResponse: (response: { data: Session[] }) => response.data,
      providesTags: ['Attendance'],
    }),
    attendanceStats: build.query<AttendanceStats | null, number | undefined>({
      query: (courseId) => ({ url: '/student/attendance/stats', params: courseId ? { course_id: courseId } : undefined }),
      transformResponse: (response: { data: AttendanceStats | null }) => response.data,
      providesTags: ['Stats'],
    }),
  }),
});

export const { useAttendanceQuery, useAttendanceStatsQuery } = attendanceApi;
