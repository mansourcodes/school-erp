import { Hourglass, Lock, RefreshCw } from 'lucide-react';
import { useBranding } from '@/entities/branding';
import type { PortalUnavailable } from '@/shared/api';
import { BASE_PATH } from '@/shared/config';
import { t } from '@/shared/i18n';

interface PortalUnavailablePageProps {
  mode: PortalUnavailable;
  onRetry: () => void;
}

/** Maintenance (503) and closed (403) screens; the PWA shell still loads (PLAN.md §4). */
export function PortalUnavailablePage({ mode, onRetry }: PortalUnavailablePageProps) {
  const maintenance = mode === 'maintenance';
  const Icon = maintenance ? Hourglass : Lock;
  const { centre } = useBranding();

  return (
    <main className="min-h-dvh grid place-items-center p-4">
      <div className="card-soft overflow-hidden w-full max-w-md">
        <div className="hero-band bg-pattern px-6 pt-8 pb-10 text-center">
          <img src={`${BASE_PATH}logo-full.svg`} alt={centre} className="h-12 mx-auto brightness-0 invert" />
        </div>
        <div className="p-6 text-center -mt-6">
          <div
            className={`arch w-16 h-20 grid place-items-center mx-auto mb-3 shadow ${maintenance ? 'bg-warning/20' : 'bg-base-200 text-primary'}`}
          >
            <Icon className="size-7" strokeWidth={1.75} aria-hidden="true" />
          </div>
          <h1 className="font-display text-lg">{t(maintenance ? 'portalMode.maintenanceTitle' : 'portalMode.closedTitle')}</h1>
          <p className="text-sm opacity-70 mt-1">{t(maintenance ? 'portalMode.maintenanceBody' : 'portalMode.closedBody')}</p>
          {maintenance && (
            <button type="button" className="btn btn-primary mt-4" onClick={onRetry}>
              <RefreshCw className="size-4" strokeWidth={1.75} aria-hidden="true" />
              {t('app.retry')}
            </button>
          )}
        </div>
      </div>
    </main>
  );
}
