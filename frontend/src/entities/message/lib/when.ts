import { t } from '@/shared/i18n';
import { fmtDate, fmtTime, toIsoDate } from '@/shared/lib';

/** "اليوم 1:20 م" / "أمس 5:10 م" / "5 أكتوبر" (PLAN.md §3.7). */
export function sentLabel(iso: string, now: Date = new Date()): string {
  const sent = new Date(iso);
  const day = toIsoDate(sent);
  const time = fmtTime(`${String(sent.getHours()).padStart(2, '0')}:${String(sent.getMinutes()).padStart(2, '0')}`);
  if (day === toIsoDate(now)) return `${t('teacher.today')} ${time}`;
  if (day === toIsoDate(new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1))) return `${t('teacher.yesterday')} ${time}`;
  return fmtDate(day, { day: 'numeric', month: 'long' });
}
