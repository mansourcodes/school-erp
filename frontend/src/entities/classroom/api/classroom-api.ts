import { baseApi } from '@/shared/api';
import type { ClassroomDetail, ClassroomStudentDetail, TeacherClassroom, TeacherStats } from '../model/types';

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
    teacherClassroomStudent: build.query<ClassroomStudentDetail, { classroomId: number; studentId: number }>({
      query: ({ classroomId, studentId }) => `/teacher/classrooms/${classroomId}/students/${studentId}`,
      transformResponse: (response: { data: ClassroomStudentDetail }) => response.data,
      // Re-read after a roster save (ClassSession) so the calendar shows the new states.
      providesTags: ['Classroom', 'ClassSession'],
    }),
    teacherStats: build.query<TeacherStats, number | undefined>({
      query: (classroomId) => ({ url: '/teacher/stats', params: classroomId ? { classroom_id: classroomId } : undefined }),
      transformResponse: (response: { data: TeacherStats }) => response.data,
      providesTags: ['Stats'],
    }),
  }),
});

export const { useTeacherClassroomsQuery, useTeacherClassroomQuery, useTeacherClassroomStudentQuery, useTeacherStatsQuery } =
  classroomApi;
