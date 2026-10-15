import { useEffect, useMemo } from 'react';
import { usePortalInfoQuery } from '../api/branding-api';
import { type Branding, readStoredBranding, storeBranding, toBranding } from '../model/branding';

/** Portal name, short name, centre name and description from Backpack Settings (last known ones offline). */
export function useBranding(): Branding {
  const { data } = usePortalInfoQuery();
  const fresh = useMemo(() => toBranding(data), [data]);

  useEffect(() => {
    if (fresh) storeBranding(fresh);
  }, [fresh]);

  return useMemo(() => fresh ?? readStoredBranding(), [fresh]);
}
