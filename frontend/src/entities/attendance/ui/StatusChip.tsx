import { t } from '@/shared/i18n';
import { ATTENDANCE_STATES } from '../config/states';
import type { AttendanceState } from '../model/types';

/** Icon + label chip (never colour alone). */
export function StatusChip({ state }: { state: AttendanceState }) {
  const { icon: Icon, cls } = ATTENDANCE_STATES[state];
  return (
    <span className={`st ${cls}`}>
      <Icon strokeWidth={1.75} aria-hidden="true" />
      {t(`attendanceState.${state}`)}
    </span>
  );
}

/** Small coded square (calendar cells, legends): ✓ ت تع ✗ غع —, hatched when excused. */
export function StatusPip({ state }: { state: AttendanceState }) {
  const { code, cls, excused } = ATTENDANCE_STATES[state];
  return (
    <span className={`pip ${cls} ${excused ? 'hatch' : ''}`} title={t(`attendanceState.${state}`)} aria-hidden="true">
      {code}
    </span>
  );
}
