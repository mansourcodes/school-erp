import { Archive, BookOpen, CalendarCheck, ChartPie, type LucideIcon, UserRound } from 'lucide-react';
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

/** Set on each route's `handle`; the top bar shows it as the page title. */
export interface ShellRouteHandle {
  title?: string;
}
