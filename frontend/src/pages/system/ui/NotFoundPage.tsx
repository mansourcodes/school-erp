import { SearchX } from 'lucide-react';
import { Link } from 'react-router';
import { routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { EmptyState } from '@/shared/ui';

export function NotFoundPage() {
  return (
    <div className="min-h-dvh grid place-items-center px-4">
      <EmptyState
        icon={SearchX}
        title={t('pages.notFoundTitle')}
        body={t('pages.notFoundBody')}
        action={
          <Link to={routes.home} className="btn btn-primary mt-2">
            {t('pages.goHome')}
          </Link>
        }
      />
    </div>
  );
}
