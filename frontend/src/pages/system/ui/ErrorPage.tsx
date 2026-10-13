import { TriangleAlert } from 'lucide-react';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

/** Route error boundary: a render error or a failed lazy chunk (e.g. after a deploy). */
export function ErrorPage() {
  return (
    <div className="min-h-dvh grid place-items-center px-4">
      <EmptyState
        icon={TriangleAlert}
        title={t('app.loadFailed')}
        action={
          <button type="button" className="btn btn-primary mt-2" onClick={() => window.location.reload()}>
            {t('app.retry')}
          </button>
        }
      />
    </div>
  );
}
