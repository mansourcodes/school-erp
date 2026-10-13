import { Archive, BookOpen, CalendarCheck, CalendarClock, ChartPie, type LucideIcon, Mail, School, UserRound } from 'lucide-react';
import type { ViewerRole } from '@/entities/viewer';
import { routes } from '@/shared/config';

export interface NavItem {
  to: string;
  /** i18n keys: full label (sidebar) and short label (dock). */
  label: string;
  short: string;
  icon: LucideIcon;
  /** Exact match only (so /attendance is not active on /attendance/stats). */
  end?: boolean;
  /** Sidebar only: students have 5 dock tabs; messages are the top-bar envelope (PLAN.md §2). */
  sidebarOnly?: boolean;
  /** Unread badge. */
  badge?: 'unread';
}

/** Student navigation: 5 dock tabs + messages in the sidebar (PLAN.md §2 "Navigation"). */
export const studentNav: NavItem[] = [
  { to: routes.home, label: 'nav.home', short: 'nav.homeShort', icon: BookOpen, end: true },
  { to: routes.attendance, label: 'nav.attendance', short: 'nav.attendanceShort', icon: CalendarCheck, end: true },
  { to: routes.stats, label: 'nav.stats', short: 'nav.statsShort', icon: ChartPie },
  { to: routes.archive, label: 'nav.archive', short: 'nav.archiveShort', icon: Archive },
  { to: routes.profile, label: 'nav.profile', short: 'nav.profileShort', icon: UserRound },
  { to: routes.messages, label: 'messages.title', short: 'messages.title', icon: Mail, sidebarOnly: true, badge: 'unread' },
];

/** Teacher navigation: 5 tabs (PLAN.md §8). */
export const teacherNav: NavItem[] = [
  { to: routes.teacher.today, label: 'teacher.navToday', short: 'teacher.navToday', icon: CalendarClock, end: true },
  { to: routes.teacher.classes, label: 'teacher.navClasses', short: 'teacher.navClasses', icon: School },
  { to: routes.teacher.messages, label: 'teacher.navMessages', short: 'teacher.navMessages', icon: Mail },
  { to: routes.teacher.stats, label: 'teacher.navStats', short: 'teacher.navStatsShort', icon: ChartPie },
  { to: routes.teacher.profile, label: 'teacher.navProfile', short: 'teacher.navProfile', icon: UserRound },
];

export const navFor = (role: ViewerRole) => (role === 'teacher' ? teacherNav : studentNav);

/** Set on each route's `handle`; the top bar shows the title. */
export interface ShellRouteHandle {
  title?: string;
  /**
   * Task screen (e.g. the attendance roster, PLAN.md §8): a back arrow replaces the logo and
   * the dock is hidden, so the page's own sticky bar owns the bottom of the screen.
   */
  task?: boolean;
}
