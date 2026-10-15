import { baseApi } from '@/shared/api';
import type { PortalInfo } from '../model/branding';

export const brandingApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    // Public, outside the portal switch: login and the maintenance screen need the theme and the names too.
    portalInfo: build.query<PortalInfo, void>({
      query: () => '/theme',
      transformResponse: (response: { data?: PortalInfo }) => response.data ?? {},
    }),
  }),
});

export const { usePortalInfoQuery } = brandingApi;
