import type { RosterState } from '@/entities/class-session';
import { baseApi } from '@/shared/api';

/** `push_pending`: students' notifications the app now sends one by one (push outbox). */
export interface SaveAttendanceResult {
  updated_at: string;
  created: boolean;
  push_pending: number;
}

export interface SaveAttendanceArgs {
  code: string;
  states: Record<number, RosterState>;
}

/** PUT /teacher/sessions/{code}: idempotent, every student of the classroom (PLAN.md §8). */
export const saveAttendanceApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    saveAttendance: build.mutation<SaveAttendanceResult, SaveAttendanceArgs>({
      query: ({ code, states }) => ({ url: `/teacher/sessions/${encodeURIComponent(code)}`, method: 'PUT', body: { states } }),
      transformResponse: (response: { data: SaveAttendanceResult }) => response.data,
      invalidatesTags: ['ClassSession', 'Today', 'Classroom', 'Stats'],
    }),
  }),
});

export const { useSaveAttendanceMutation } = saveAttendanceApi;
