import { createBrowserRouter, type RouteObject } from 'react-router';
import { HomePage } from '@/pages/student/home';
import { ErrorPage, NotFoundPage } from '@/pages/system';
import { AppShell, type ShellRouteHandle } from '@/widgets/app-shell';
import { BASE_PATH, routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { GuestOnly, RequireRole } from './guards';

const title = (key: string): ShellRouteHandle => ({ title: t(key) });

// Screens past the first one are code-split, so a phone on mobile data
// downloads them only when opened (PLAN.md §4 "Mobile performance budget").
const comingSoon = () => import('@/pages/system').then((m) => ({ Component: m.ComingSoonPage }));

export const appRoutes: RouteObject[] = [
  {
    errorElement: <ErrorPage />,
    children: [
      {
        // Split too: a signed-in student never downloads the login form, its validation or Turnstile.
        element: <GuestOnly />,
        children: [{ path: routes.login, lazy: () => import('@/pages/login').then((m) => ({ Component: m.LoginPage })) }],
      },
      {
        element: <RequireRole role="student" />,
        children: [
          {
            element: <AppShell />,
            children: [
              { index: true, element: <HomePage />, handle: title('nav.home') },
              { path: routes.attendance, lazy: comingSoon, handle: title('nav.attendance') },
              { path: routes.stats, lazy: comingSoon, handle: title('nav.stats') },
              { path: routes.archive, lazy: comingSoon, handle: title('nav.archive') },
              {
                path: routes.profile,
                lazy: () => import('@/pages/student/profile').then((m) => ({ Component: m.ProfilePage })),
                handle: title('nav.profile'),
              },
            ],
          },
        ],
      },
      { path: '*', element: <NotFoundPage /> },
    ],
  },
];

export function createAppRouter() {
  return createBrowserRouter(appRoutes, { basename: BASE_PATH.replace(/\/$/, '') });
}
