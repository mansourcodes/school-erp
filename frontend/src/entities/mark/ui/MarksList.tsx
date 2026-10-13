import { Award, GraduationCap, Info } from 'lucide-react';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';
import { type Mark, marksTotals } from '../model/types';

interface MarksListProps {
  marks: Mark[];
  published: boolean;
  totalMark: number | null;
  finalGrade: string | null;
}

/** `brief_marks` rows as list items with a thin progress bar (PLAN.md §2 phone patterns). */
export function MarksList({ marks, published, totalMark, finalGrade }: MarksListProps) {
  if (!marks.some((m) => m.mark !== null)) {
    return <EmptyState icon={GraduationCap} title={t('marks.noneTitle')} body={t('marks.noneBody')} />;
  }

  const totals = marksTotals(marks);
  const total = published && totalMark !== null ? totalMark : totals.sum;

  return (
    <div className="space-y-2">
      {published ? (
        <div className="grid grid-cols-2 gap-3">
          <div className="rounded-box p-4 bg-base-200">
            <p className="text-sm opacity-70">{t('marks.total')}</p>
            <p className="text-2xl sm:text-3xl font-semibold num whitespace-nowrap">
              {total}
              <span className="text-sm sm:text-base opacity-50"> / {totals.max}</span>
            </p>
          </div>
          <div className="rounded-box p-4 bg-accent/20 border border-accent/30">
            <p className="text-sm opacity-70">{t('marks.grade')}</p>
            <p className="text-xl font-display mt-1 flex items-center gap-2">
              <Award className="size-6 text-accent" strokeWidth={1.75} aria-hidden="true" />
              {finalGrade}
            </p>
          </div>
        </div>
      ) : (
        <div role="status" className="alert alert-soft alert-info text-sm">
          <Info className="size-5" strokeWidth={1.75} aria-hidden="true" />
          <span>{t('marks.inProgress', { sum: totals.sum, max: totals.max })}</span>
        </div>
      )}

      <ul className="divide-y divide-base-300">
        {marks.map((m) => (
          <li key={m.key} className="py-3">
            <div className="flex items-baseline justify-between gap-3">
              <span className="font-medium">{m.label}</span>
              <span className="num whitespace-nowrap">
                <b>{m.mark ?? '—'}</b> <span className="opacity-50 text-sm">/ {m.max ?? '—'}</span>
              </span>
            </div>
            {m.mark !== null && m.max ? (
              <div className="h-1.5 rounded-full bg-base-200 mt-2 overflow-hidden">
                <div className="h-full rounded-full bg-primary" style={{ width: `${Math.min(100, (m.mark / m.max) * 100)}%` }} />
              </div>
            ) : (
              <p className="text-xs opacity-50 mt-1">{t('marks.notYet')}</p>
            )}
          </li>
        ))}
      </ul>

      <div className="flex items-baseline justify-between border-t-2 border-base-300 pt-3 font-semibold">
        <span>{t('marks.total')}</span>
        <span className="num">
          {total} / {totals.max}
        </span>
      </div>
    </div>
  );
}
