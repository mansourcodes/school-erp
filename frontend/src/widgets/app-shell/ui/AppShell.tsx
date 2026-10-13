import { ArrowRight, ArrowUpRight, Mail, WifiOff } from 'lucide-react';
import { NavLink, Outlet, useMatches, useNavigate } from 'react-router';
import { LogoutButton } from '@/features/auth/logout';
import { InstallBanner } from '@/features/install-app';
import { ThemeCycleButton, ThemeSegment } from '@/features/theme-switch';
import { useMeQuery, type Viewer, ViewerAvatar } from '@/entities/viewer';
import { BASE_PATH, homeOf, LEGACY_PORTAL_URL, routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { useOnlineStatus } from '@/shared/lib';
import { navFor, type ShellRouteHandle } from '../config/nav';

/**
 * Phones (≈90% of visits): sticky top bar + bottom dock in thumb reach.
 * lg+: the dock becomes a sidebar on the start (right) side (PLAN.md §2 "Layout").
 * Role-aware: the student and teacher panels share this shell with their own navigation.
 */
export function AppShell() {
  const { data: viewer } = useMeQuery();
  const online = useOnlineStatus();
  const navigate = useNavigate();
  const handles = useMatches().map((match) => match.handle as ShellRouteHandle | undefined);
  const title = handles.map((h) => h?.title).filter(Boolean).at(-1);
  const task = handles.some((h) => h?.task);

  if (!viewer) return null;
  const nav = navFor(viewer.role);
  const home = homeOf(viewer.role);
  const profile = viewer.role === 'teacher' ? routes.teacher.profile : routes.profile;

  return (
    <div className="min-h-dvh lg:ps-72">
      <Sidebar viewer={viewer} />

      <header className="sticky top-0 z-30 bg-base-100/90 backdrop-blur border-b border-base-300 pt-safe">
        <div className="flex items-center gap-3 px-4 lg:px-8 h-14 lg:h-16 max-w-6xl mx-auto w-full">
          {task ? (
            <button
              type="button"
              className="btn btn-ghost btn-circle -ms-2 shrink-0"
              aria-label={t('teacher.back')}
              onClick={() => (window.history.length > 1 ? navigate(-1) : navigate(home))}
            >
              <ArrowRight className="size-5" strokeWidth={1.75} aria-hidden="true" />
            </button>
          ) : (
            <NavLink to={home} className="lg:hidden shrink-0" aria-label={t('app.home')}>
              <img src={`${BASE_PATH}logo-icon.svg`} alt="" className="h-9 w-auto" />
            </NavLink>
          )}
          <div className="flex-1 min-w-0">
            <p className="text-xs opacity-60 leading-none mb-1 flex items-center gap-1.5">
              {viewer.role === 'teacher' ? t('teacher.panel') : t('app.studentPortal')}
              <span className="badge badge-accent badge-xs">{t('app.beta')}</span>
            </p>
            <h1 className="font-display text-base sm:text-lg leading-tight truncate">{title}</h1>
          </div>
          {viewer.role === 'student' && (
            // Messages: envelope + unread badge on every screen size (PLAN.md §2 "Navigation").
            <NavLink
              to={routes.messages}
              className="btn btn-ghost btn-circle relative"
              aria-label={viewer.unread_messages ? t('messages.inboxLabel', { n: viewer.unread_messages }) : t('messages.title')}
            >
              <Mail className="size-5" strokeWidth={1.75} aria-hidden="true" />
              {viewer.unread_messages > 0 && (
                <span className="badge badge-error badge-xs absolute top-1.5 end-1.5 num px-1 min-w-4">{viewer.unread_messages}</span>
              )}
            </NavLink>
          )}
          <ThemeCycleButton className="lg:hidden" />
          <NavLink to={profile} className="lg:hidden" aria-label={t('nav.profile')}>
            <ViewerAvatar viewer={viewer} className="w-8 h-10" textClassName="text-sm" />
          </NavLink>
        </div>
        {!online && (
          <div role="status" className="bg-warning text-warning-content text-sm px-4 py-1.5 flex items-center justify-center gap-2">
            <WifiOff className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t('app.offline')}
          </div>
        )}
      </header>

      <main className={`max-w-6xl mx-auto w-full px-4 lg:px-8 pt-4 lg:pt-8 ${task ? 'pb-36' : 'pb-dock'}`}>
        {!task && <InstallBanner />}
        <Outlet />
      </main>

      {!task && (
        <nav className="dock dock-md bg-base-100/90 backdrop-blur border-t border-base-300 lg:hidden z-40" aria-label={t('nav.main')}>
          {nav.filter((item) => !item.sidebarOnly).map(({ to, short, icon: Icon, end }) => (
            <NavLink key={to} to={to} end={end} className={({ isActive }) => (isActive ? 'dock-active text-primary' : 'opacity-70')}>
              <Icon className="size-5" strokeWidth={1.75} aria-hidden="true" />
              <span className="dock-label">{t(short)}</span>
            </NavLink>
          ))}
        </nav>
      )}
    </div>
  );
}

function Sidebar({ viewer }: { viewer: Viewer }) {
  return (
    <aside className="hidden lg:flex fixed inset-y-0 start-0 z-40 w-72 bg-base-100 border-e border-base-300 flex-col overflow-y-auto">
      <NavLink to={homeOf(viewer.role)} className="px-6 pt-6 pb-4 block" aria-label={t('app.home')}>
        <img src={`${BASE_PATH}logo-full.svg`} alt={t('app.centre')} className="h-14 w-auto" />
      </NavLink>

      <div className="mx-4 mb-5 rounded-box hero-band bg-pattern p-4 flex items-center gap-3">
        <ViewerAvatar viewer={viewer} className="w-11 h-14" />
        <div className="min-w-0">
          <p className="text-xs opacity-80">{t('app.greeting')}</p>
          <p className="font-semibold truncate">{viewer.first_name}</p>
          <p className="text-xs opacity-80 num">
            {viewer.role === 'student' ? t('app.studentNumber', { id: viewer.student_id }) : t('teacher.role')}
          </p>
        </div>
      </div>

      <ul className="menu w-full px-3 gap-1 text-[0.95rem]">
        {navFor(viewer.role).map(({ to, label, icon: Icon, end, badge }) => (
          <li key={to}>
            <NavLink to={to} end={end} className={({ isActive }) => `py-2.5 ${isActive ? 'menu-active' : ''}`}>
              <Icon className="size-5" strokeWidth={1.75} aria-hidden="true" />
              {t(label)}
              {badge === 'unread' && viewer.role === 'student' && viewer.unread_messages > 0 && (
                <span className="badge badge-error badge-sm ms-auto num">{viewer.unread_messages}</span>
              )}
            </NavLink>
          </li>
        ))}
      </ul>

      <div className="mt-auto p-4 space-y-3">
        <ThemeSegment />
        {viewer.role === 'student' && (
          <a href={LEGACY_PORTAL_URL} className="btn btn-ghost btn-sm btn-block justify-start opacity-80">
            <ArrowUpRight className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t('app.backToLegacy')}
          </a>
        )}
        <LogoutButton className="btn-block justify-start text-error" />
      </div>
    </aside>
  );
}
