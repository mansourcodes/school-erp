import { type Viewer, viewerApi } from '@/entities/viewer';
import { baseApi, sessionAuthenticated } from '@/shared/api';

export interface LoginRequest {
  role: 'student' | 'teacher';
  /** student */
  cpr?: string;
  /** teacher */
  email?: string;
  password: string;
  /** Honeypot; humans never fill it (PLAN.md §6). */
  website?: string;
  turnstile_token?: string;
}

export const loginApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    login: build.mutation<Viewer, LoginRequest>({
      query: (body) => ({ url: '/auth/login', method: 'POST', body }),
      transformResponse: (response: { data: Viewer }) => response.data,
      async onQueryStarted(_arg, { dispatch, queryFulfilled }) {
        try {
          const { data } = await queryFulfilled;
          // The login answer is the viewer: no extra /me round trip on a phone.
          dispatch(viewerApi.util.upsertQueryData('me', undefined, data));
          dispatch(sessionAuthenticated());
        } catch {
          // Handled by the form.
        }
      },
    }),
  }),
});

export const { useLoginMutation } = loginApi;
