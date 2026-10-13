import { baseApi } from '@/shared/api';

export const logoutApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    logout: build.mutation<void, void>({
      query: () => ({ url: '/auth/logout', method: 'POST' }),
    }),
  }),
});

export const { useLogoutMutation } = logoutApi;
