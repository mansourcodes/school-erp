import { baseApi, sessionAuthenticated } from '@/shared/api';
import type { Viewer } from '../model/types';

export const viewerApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    me: build.query<Viewer, void>({
      query: () => '/me',
      transformResponse: (response: { data: Viewer }) => response.data,
      providesTags: ['Viewer'],
      async onQueryStarted(_arg, { dispatch, queryFulfilled }) {
        try {
          await queryFulfilled;
          dispatch(sessionAuthenticated());
        } catch {
          // Not signed in: the route guard handles it.
        }
      },
    }),
  }),
});

export const { useMeQuery } = viewerApi;
