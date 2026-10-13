import { t } from '@/shared/i18n';

/** Full-screen placeholder while the session is resolved. */
export function Splash() {
  return (
    <div className="min-h-dvh grid place-items-center" role="status">
      <div className="flex flex-col items-center gap-4">
        <img src={`${import.meta.env.BASE_URL}logo-icon.svg`} alt="" className="h-16 w-auto" />
        <span className="loading loading-dots loading-md text-primary" aria-hidden="true" />
        <span className="sr-only">{t('app.loading')}</span>
      </div>
    </div>
  );
}
