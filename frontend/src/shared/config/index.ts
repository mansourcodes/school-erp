/** Asset base path; Vite injects it from `base` ('/beta/'). The build stays in public/beta in every mode. */
export const BASE_PATH = import.meta.env.BASE_URL;

/**
 * Page base, decided at runtime: '/beta' during the beta, '' once `portal.mode` is live and Laravel serves
 * the pages at the root (PortalMode::appPath). Taken from the address, so a cached shell agrees with it.
 */
export const APP_BASE = /^\/beta(\/|$)/.test(window.location.pathname) ? '/beta' : '';

/** During the beta the old portal is still there to go back to; once live it redirects here. */
export const IS_BETA = APP_BASE !== '';

/** The service worker's scope: it controls the pages, so it follows APP_BASE ('/sw.js' is served when live). */
export const SW_SCOPE = `${APP_BASE}/`;
export const SW_URL = APP_BASE ? `${BASE_PATH}sw.js` : '/sw.js';

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
  messages: '/messages',
  teacher: {
    today: '/teacher',
    classes: '/teacher/classes',
    classroom: (id: number | string) => `/teacher/classes/${id}`,
    roster: (code: string) => `/teacher/attendance/${encodeURIComponent(code)}`,
    stats: '/teacher/stats',
    messages: '/teacher/messages',
    /** After a save or a message: sends the students' notifications one by one. */
    notify: '/teacher/notify',
    profile: '/teacher/profile',
  },
} as const;

/** Where each role lands after login. */
export const homeOf = (role: 'student' | 'teacher') => (role === 'teacher' ? routes.teacher.today : routes.home);
