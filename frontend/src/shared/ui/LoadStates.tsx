import { RefreshCw, WifiOff } from 'lucide-react';
import { t } from '@/shared/i18n';
import { EmptyState } from './EmptyState';

/** Skeleton blocks while a screen loads. */
export function LoadingBlocks({ heights = ['h-28', 'h-48', 'h-48'] }: { heights?: string[] }) {
  return (
    <div className="space-y-4" aria-busy="true">
      {heights.map((h, i) => (
        <div key={i} className={`skeleton w-full rounded-box ${h}`} />
      ))}
      <span className="sr-only" role="status">
        {t('app.loading')}
      </span>
    </div>
  );
}

/** "تعذّر تحميل البيانات" with a retry. */
export function ErrorCard({ onRetry }: { onRetry: () => void }) {
  return (
    <div className="card-soft">
      <EmptyState
        icon={WifiOff}
        title={t('app.loadFailed')}
        body={t('course.errorBody')}
        action={
          <button type="button" className="btn btn-primary mt-2" onClick={onRetry}>
            <RefreshCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
            {t('app.retry')}
          </button>
        }
      />
    </div>
  );
}
