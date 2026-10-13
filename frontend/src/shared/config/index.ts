/** SPA base path; Vite injects it from `base` ('/beta/'). */
export const BASE_PATH = import.meta.env.BASE_URL;

/** Absolute URLs, so requests also resolve outside a browser (tests). */
export const API_BASE_URL = `${window.location.origin}/api/v1`;
export const CSRF_COOKIE_URL = `${window.location.origin}/sanctum/csrf-cookie`;

/** Cloudflare Turnstile site key; empty disables the widget (local development). */
export const TURNSTILE_SITE_KEY: string = import.meta.env.VITE_TURNSTILE_SITE_KEY ?? '';

/** The current Blade portal stays active during the beta (PLAN.md §4). */
export const LEGACY_PORTAL_URL = '/dashboard';

/** In-app routes, relative to the router basename. */
export const routes = {
  login: '/login',
  home: '/',
  attendance: '/attendance',
  stats: '/attendance/stats',
  archive: '/archive',
  profile: '/profile',
  teacher: {
    today: '/teacher',
    classes: '/teacher/classes',
    classroom: (id: number | string) => `/teacher/classes/${id}`,
    roster: (code: string) => `/teacher/attendance/${encodeURIComponent(code)}`,
    stats: '/teacher/stats',
    profile: '/teacher/profile',
  },
} as const;

/** Where each role lands after login. */
export const homeOf = (role: 'student' | 'teacher') => (role === 'teacher' ? routes.teacher.today : routes.home);
