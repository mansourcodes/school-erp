import { useEffect } from 'react';
import { usePortalThemeQuery } from '../api/portal-theme-api';
import { applyTheme } from '../model/themes';

/**
 * Applies the centre's theme (Backpack setting `portal.theme`). theme-boot.js already painted the last
 * known one; offline or on an error that one stays.
 */
export function usePortalTheme() {
  const { data } = usePortalThemeQuery();

  useEffect(() => {
    if (data) applyTheme(data);
  }, [data]);
}
