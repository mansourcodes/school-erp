import { CloudOff } from 'lucide-react';
import { Navigate, Outlet, useLocation } from 'react-router';
import { type ViewerRole, useMeQuery } from '@/entities/viewer';
import { PortalUnavailablePage } from '@/pages/system';
import { baseApi, portalAvailable, selectPortalUnavailable, selectSessionStatus, toApiError } from '@/shared/api';
import { homeOf, routes } from '@/shared/config';
import { t } from '@/shared/i18n';
import { useAppDispatch, useAppSelector } from '@/shared/lib';
import { EmptyState, Splash } from '@/shared/ui';

/** Signed-in area for one role. A timed-out session lands on /login?expired=1. */
export function RequireRole({ role }: { role: ViewerRole }) {
  const location = useLocation();
  const sessionStatus = useAppSelector(selectSessionStatus);
  const { data: viewer, error, isLoading, refetch } = useMeQuery(undefined, { skip: sessionStatus === 'expired' });

  if (sessionStatus === 'expired') {
    return <Navigate to={`${routes.login}?expired=1`} replace />;
  }
  if (isLoading) return <Splash />;

  if (!viewer) {
    const status = toApiError(error).status;
    if (status === 401 || status === 419) {
      return <Navigate to={routes.login} replace state={{ from: location.pathname + location.search }} />;
    }
    // Offline or a server error: keep the user here with a retry, not on the login screen.
    return (
      <div className="min-h-dvh grid place-items-center px-4">
        <EmptyState
          icon={CloudOff}
          title={t('app.loadFailed')}
          action={
            <button type="button" className="btn btn-primary mt-2" onClick={() => refetch()}>
              {t('app.retry')}
            </button>
          }
        />
      </div>
    );
  }

  // The other role's area: go to your own home.
  if (viewer.role !== role) return <Navigate to={homeOf(viewer.role)} replace />;
  return <Outlet />;
}

/** The portal switch (maintenance / closed) replaces every screen until it is lifted. */
export function PortalGate() {
  const unavailable = useAppSelector(selectPortalUnavailable);
  const dispatch = useAppDispatch();

  if (!unavailable) return <Outlet />;
  return (
    <PortalUnavailablePage
      mode={unavailable}
      onRetry={() => {
        dispatch(portalAvailable());
        // Refetch everything; if still in maintenance, the API puts the screen back.
        dispatch(baseApi.util.resetApiState());
      }}
    />
  );
}

/** The login screen is for signed-out users only. */
export function GuestOnly() {
  const sessionStatus = useAppSelector(selectSessionStatus);
  const { data: viewer, isLoading } = useMeQuery(undefined, { skip: sessionStatus === 'expired' });

  if (isLoading) return <Splash />;
  if (viewer) return <Navigate to={homeOf(viewer.role)} replace />;
  return <Outlet />;
}
