import { useEffect } from 'react';
import { useBranding, usePortalInfoQuery } from '@/entities/branding';
import { applyTheme, DEFAULT_THEME, isPortalTheme } from '../model/themes';

/**
 * Applies the centre's theme (Backpack setting `portal.theme`). theme-boot.js already painted the last
 * known one; offline or on an error that one stays. Also the tab title (`portal.name`, when no screen
 * set its own) and the iOS home-screen name (`portal.short_name`).
 */
export function usePortalTheme() {
  const { data } = usePortalInfoQuery();
  const { name, short_name } = useBranding();

  useEffect(() => {
    if (data) applyTheme(isPortalTheme(data.theme) ? data.theme : DEFAULT_THEME);
  }, [data]);

  // Laravel fills both on deep links; /beta/ and the worker's cached shell are the static file with them empty.
  useEffect(() => {
    if (name && !document.title) document.title = name;
  }, [name]);

  useEffect(() => {
    const meta = document.querySelector<HTMLMetaElement>('meta[name="apple-mobile-web-app-title"]');
    if (meta && short_name) meta.content = short_name;
  }, [short_name]);
}
