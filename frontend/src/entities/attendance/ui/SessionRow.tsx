import { t } from '@/shared/i18n';
import { fmtDate, fmtTime } from '@/shared/lib';
import type { Session } from '../model/types';
import { StatusChip } from './StatusChip';

/** One session: subject, (date ·) time · teacher, status chip. */
export function SessionRow({ session, withDate = false }: { session: Session; withDate?: boolean }) {
  const meta = [
    withDate ? fmtDate(session.date, { weekday: 'short', day: 'numeric', month: 'short' }) : null,
    session.start ? fmtTime(session.start) : null,
    session.teacher,
  ]
    .filter(Boolean)
    .join(' · ');

  return (
    <div className="flex gap-3 items-start">
      <div className="flex-1 min-w-0">
        <p className="font-medium leading-snug">{session.curriculum}</p>
        {meta && <p className="text-xs opacity-70 num">{meta}</p>}
        {session.other_classroom && <p className="text-xs opacity-60">{t('attendance.otherClassroom')}</p>}
      </div>
      <StatusChip state={session.state} />
    </div>
  );
}
