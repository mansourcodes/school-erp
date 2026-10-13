import { Archive, BookOpen, CalendarCheck, CalendarClock, ChartPie, type LucideIcon, School, UserRound } from 'lucide-react';
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
}

/** Student navigation: 5 dock tabs (PLAN.md §2 "Navigation"). Messages join in P3.3. */
export const studentNav: NavItem[] = [
  { to: routes.home, label: 'nav.home', short: 'nav.homeShort', icon: BookOpen, end: true },
  { to: routes.attendance, label: 'nav.attendance', short: 'nav.attendanceShort', icon: CalendarCheck, end: true },
  { to: routes.stats, label: 'nav.stats', short: 'nav.statsShort', icon: ChartPie },
  { to: routes.archive, label: 'nav.archive', short: 'nav.archiveShort', icon: Archive },
  { to: routes.profile, label: 'nav.profile', short: 'nav.profileShort', icon: UserRound },
];

/** Teacher navigation (PLAN.md §8). Messages join in P3.3. */
export const teacherNav: NavItem[] = [
  { to: routes.teacher.today, label: 'teacher.navToday', short: 'teacher.navToday', icon: CalendarClock, end: true },
  { to: routes.teacher.classes, label: 'teacher.navClasses', short: 'teacher.navClasses', icon: School },
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
