import {
  type BaseQueryFn,
  createApi,
  type FetchArgs,
  fetchBaseQuery,
  type FetchBaseQueryError,
} from '@reduxjs/toolkit/query/react';
import { API_BASE_URL, CSRF_COOKIE_URL } from '@/shared/config';
import { readCookie } from '@/shared/lib';
import { portalUnavailable } from './portal-status';
import { selectSessionStatus, sessionExpired } from './session';

const rawBaseQuery = fetchBaseQuery({
  baseUrl: API_BASE_URL,
  // Sanctum SPA auth: HttpOnly session cookie; tokens never touch JS (PLAN.md §4, §6).
  credentials: 'include',
  prepareHeaders: (headers) => {
    headers.set('Accept', 'application/json');
    headers.set('X-Requested-With', 'XMLHttpRequest');
    const xsrf = readCookie('XSRF-TOKEN');
    if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
    return headers;
  },
});

// Their 401/419 answers are handled by the calling feature, not as an expired session.
const AUTH_ENDPOINTS = new Set(['login', 'logout']);

/**
 * - 419 (CSRF token gone with the server session; the remember cookie still signs the user in):
 *   fetch a fresh CSRF cookie and retry once.
 * - 401/419 after a successful sign-in → session expired (logged out elsewhere, password changed):
 *   clear all cached data; the route guard then sends the user to /login?expired=1.
 * - 429 → copy `Retry-After` into the error body so the UI can count down.
 */
const baseQueryWithAuth: BaseQueryFn<string | FetchArgs, unknown, FetchBaseQueryError> = async (
  args,
  api,
  extraOptions,
) => {
  let result = await rawBaseQuery(args, api, extraOptions);
  if (result.error?.status === 419) {
    try {
      await ensureCsrfCookie();
      result = await rawBaseQuery(args, api, extraOptions);
    } catch {
      // Offline or server down: handled below like any other error.
    }
  }
  const { error } = result;
  if (!error) return result;

  if ((error.status === 401 || error.status === 419) && !AUTH_ENDPOINTS.has(api.endpoint)) {
    if (selectSessionStatus(api.getState() as RootState) === 'authenticated') {
      api.dispatch(sessionExpired());
      api.dispatch(baseApi.util.resetApiState());
    }
  }

  // Portal switch: the whole app shows the maintenance / closed screen.
  const code = typeof error.data === 'object' && error.data !== null ? (error.data as { code?: unknown }).code : undefined;
  if (error.status === 503 && code === 'portal_maintenance') api.dispatch(portalUnavailable('maintenance'));
  if (error.status === 403 && code === 'portal_closed') api.dispatch(portalUnavailable('closed'));

  if (error.status === 429) {
    const retryAfter = Number(result.meta?.response?.headers.get('Retry-After'));
    if (retryAfter > 0 && typeof error.data === 'object' && error.data !== null && !('retry_after' in error.data)) {
      error.data = { ...error.data, retry_after: retryAfter };
    }
  }

  return result;
};

/** Empty base API; each slice injects its own endpoints (PLAN.md §4 "RTK Query in FSD"). */
export const baseApi = createApi({
  reducerPath: 'api',
  baseQuery: baseQueryWithAuth,
  tagTypes: ['Viewer', 'Course', 'Attendance', 'Stats', 'Classroom', 'ClassSession', 'Today', 'Inbox', 'Sent'],
  endpoints: () => ({}),
});

/** Sets the XSRF-TOKEN cookie; call before the first state-changing request. */
export async function ensureCsrfCookie(): Promise<void> {
  const response = await fetch(CSRF_COOKIE_URL, { credentials: 'include', headers: { Accept: 'application/json' } });
  if (!response.ok) throw new Error(`csrf-cookie: ${response.status}`);
}
