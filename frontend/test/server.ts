import { http, HttpResponse } from 'msw';
import { setupServer } from 'msw/node';

/** Shaped like MeResource (PLAN.md §7.1). */
export const demoViewer = {
  role: 'student' as const,
  id: 158,
  name: 'حسين علي عبدالله الحايكي',
  first_name: 'حسين',
  student_id: '20240158',
  photo_url: null,
};

// Default: signed out. Tests override handlers per scenario.
export const server = setupServer(
  http.get('*/sanctum/csrf-cookie', () => {
    document.cookie = 'XSRF-TOKEN=token%3D123';
    return new HttpResponse(null, { status: 204 });
  }),
  http.get('*/api/v1/me', () => HttpResponse.json({ message: 'يجب تسجيل الدخول أولًا' }, { status: 401 })),
);
