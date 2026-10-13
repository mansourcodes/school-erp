import { baseApi } from '@/shared/api';
import type { ClassroomDetail, TeacherClassroom, TeacherStats } from '../model/types';

export const classroomApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    teacherClassrooms: build.query<TeacherClassroom[], void>({
      query: () => '/teacher/classrooms',
      transformResponse: (response: { data: TeacherClassroom[] }) => response.data,
      providesTags: ['Classroom'],
    }),
    teacherClassroom: build.query<ClassroomDetail, number>({
      query: (id) => `/teacher/classrooms/${id}`,
      transformResponse: (response: { data: ClassroomDetail }) => response.data,
      providesTags: (_r, _e, id) => ['Classroom', { type: 'Classroom', id }],
    }),
    teacherStats: build.query<TeacherStats, number | undefined>({
      query: (classroomId) => ({ url: '/teacher/stats', params: classroomId ? { classroom_id: classroomId } : undefined }),
      transformResponse: (response: { data: TeacherStats }) => response.data,
      providesTags: ['Stats'],
    }),
  }),
});

export const { useTeacherClassroomsQuery, useTeacherClassroomQuery, useTeacherStatsQuery } = classroomApi;
