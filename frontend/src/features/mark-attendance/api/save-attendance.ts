import type { RosterState } from '@/entities/class-session';
import { baseApi } from '@/shared/api';

export interface SaveAttendanceArgs {
  code: string;
  states: Record<number, RosterState>;
}

/** PUT /teacher/sessions/{code}: idempotent, every student of the classroom (PLAN.md §8). */
export const saveAttendanceApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    saveAttendance: build.mutation<{ updated_at: string; created: boolean }, SaveAttendanceArgs>({
      query: ({ code, states }) => ({ url: `/teacher/sessions/${encodeURIComponent(code)}`, method: 'PUT', body: { states } }),
      transformResponse: (response: { data: { updated_at: string; created: boolean } }) => response.data,
      invalidatesTags: ['ClassSession', 'Today', 'Classroom', 'Stats'],
    }),
  }),
});

export const { useSaveAttendanceMutation } = saveAttendanceApi;
