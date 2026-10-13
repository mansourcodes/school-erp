import { baseApi } from '@/shared/api';
import type { Roster, TeacherSession, TeacherToday } from '../model/types';

export const classSessionApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    teacherToday: build.query<TeacherToday, void>({
      query: () => '/teacher/today',
      transformResponse: (response: { data: TeacherToday }) => response.data,
      providesTags: ['Today'],
    }),
    classroomSessions: build.query<TeacherSession[], number>({
      query: (classroomId) => `/teacher/classrooms/${classroomId}/sessions`,
      transformResponse: (response: { data: TeacherSession[] }) => response.data,
      providesTags: (_r, _e, id) => [{ type: 'ClassSession', id: `classroom-${id}` }],
    }),
    roster: build.query<Roster, string>({
      query: (code) => `/teacher/sessions/${encodeURIComponent(code)}`,
      transformResponse: (response: { data: Roster }) => response.data,
      providesTags: (_r, _e, code) => [{ type: 'ClassSession', id: code }],
    }),
  }),
});

export const { useTeacherTodayQuery, useClassroomSessionsQuery, useRosterQuery } = classSessionApi;
