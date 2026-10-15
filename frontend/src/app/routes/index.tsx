import { createBrowserRouter, type RouteObject } from 'react-router';
import { HomePage } from '@/pages/student/home';
import { ErrorPage, NotFoundPage } from '@/pages/system';
import { AppShell, type ShellRouteHandle } from '@/widgets/app-shell';
import { APP_BASE, routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { GuestOnly, PortalGate, RequireRole } from './guards';

const title = (key: string): ShellRouteHandle => ({ title: t(key) });
const task = (key: string): ShellRouteHandle => ({ title: t(key), task: true });

// Screens past the first one are code-split, so a phone on mobile data
// downloads them only when opened (PLAN.md §4 "Mobile performance budget").
const archive = () => import('@/pages/student/archive').then((m) => ({ Component: m.ArchivePage }));

export const appRoutes: RouteObject[] = [
  {
    element: <PortalGate />,
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
              { path: routes.attendance, lazy: () => import('@/pages/student/attendance').then((m) => ({ Component: m.AttendancePage })), handle: title('nav.attendance') },
              { path: routes.stats, lazy: () => import('@/pages/student/stats').then((m) => ({ Component: m.StatsPage })), handle: title('nav.stats') },
              { path: routes.archive, lazy: archive, handle: title('nav.archive') },
              { path: `${routes.archive}/:courseId`, lazy: archive, handle: title('nav.archive') },
              {
                path: routes.messages,
                lazy: () => import('@/pages/student/messages').then((m) => ({ Component: m.MessagesPage })),
                handle: title('messages.title'),
              },
              {
                path: routes.profile,
                lazy: () => import('@/pages/student/profile').then((m) => ({ Component: m.ProfilePage })),
                handle: title('nav.profile'),
              },
            ],
          },
        ],
      },
      {
        // Teacher panel (PLAN.md §8), same SPA and shell.
        element: <RequireRole role="teacher" />,
        children: [
          {
            element: <AppShell />,
            children: [
              {
                path: routes.teacher.today,
                lazy: () => import('@/pages/teacher/today').then((m) => ({ Component: m.TeacherTodayPage })),
                handle: title('teacher.navToday'),
              },
              {
                path: `${routes.teacher.today}/attendance/:code`,
                lazy: () => import('@/pages/teacher/roster').then((m) => ({ Component: m.TeacherRosterPage })),
                handle: task('teacher.rosterTitle'),
              },
              {
                path: routes.teacher.classes,
                lazy: () => import('@/pages/teacher/classes').then((m) => ({ Component: m.TeacherClassesPage })),
                handle: title('teacher.navClasses'),
              },
              {
                path: `${routes.teacher.classes}/:id`,
                lazy: () => import('@/pages/teacher/classroom').then((m) => ({ Component: m.TeacherClassroomPage })),
                handle: task('teacher.classroomTitle'),
              },
              {
                path: routes.teacher.messages,
                lazy: () => import('@/pages/teacher/messages').then((m) => ({ Component: m.TeacherMessagesPage })),
                handle: title('teacher.navMessages'),
              },
              {
                path: `${routes.teacher.messages}/new`,
                lazy: () => import('@/pages/teacher/compose').then((m) => ({ Component: m.TeacherComposePage })),
                handle: task('messages.new'),
              },
              {
                path: routes.teacher.notify,
                lazy: () => import('@/pages/teacher/notify').then((m) => ({ Component: m.TeacherNotifyPage })),
                handle: task('notify.title'),
              },
              {
                path: routes.teacher.stats,
                lazy: () => import('@/pages/teacher/stats').then((m) => ({ Component: m.TeacherStatsPage })),
                handle: title('teacher.navStats'),
              },
              {
                path: routes.teacher.profile,
                lazy: () => import('@/pages/teacher/profile').then((m) => ({ Component: m.TeacherProfilePage })),
                handle: title('teacher.navProfile'),
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
  return createBrowserRouter(appRoutes, { basename: APP_BASE });
}
