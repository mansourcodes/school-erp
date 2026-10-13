import { baseApi } from '@/shared/api';

export const logoutApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    logout: build.mutation<void, void>({
      query: () => ({ url: '/auth/logout', method: 'POST' }),
    }),
    unsubscribePush: build.mutation<void, string>({
      query: (endpoint) => ({ url: '/push/subscriptions', method: 'DELETE', body: { endpoint } }),
    }),
  }),
});

export const { useLogoutMutation, useUnsubscribePushMutation } = logoutApi;
