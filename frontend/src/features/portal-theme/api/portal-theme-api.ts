import { baseApi } from '@/shared/api';
import { DEFAULT_THEME, isPortalTheme, type PortalTheme } from '../model/themes';

export const portalThemeApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    // Public, outside the portal switch: login and the maintenance screen are themed too.
    portalTheme: build.query<PortalTheme, void>({
      query: () => '/theme',
      transformResponse: (response: { data?: { theme?: unknown } }) =>
        isPortalTheme(response.data?.theme) ? response.data.theme : DEFAULT_THEME,
    }),
  }),
});

export const { usePortalThemeQuery } = portalThemeApi;
