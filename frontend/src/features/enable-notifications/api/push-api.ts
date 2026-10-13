import { baseApi } from '@/shared/api';

export interface NotificationSettings {
  attendance: boolean;
  messages: boolean;
}

export const pushApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    pushConfig: build.query<{ enabled: boolean; public_key: string | null }, void>({
      query: () => '/push/config',
      transformResponse: (response: { data: { enabled: boolean; public_key: string | null } }) => response.data,
    }),
    subscribePush: build.mutation<void, PushSubscriptionJSON>({
      query: (subscription) => ({
        url: '/push/subscriptions',
        method: 'POST',
        body: { endpoint: subscription.endpoint, keys: subscription.keys, content_encoding: 'aes128gcm' },
      }),
    }),
    notificationSettings: build.query<NotificationSettings, void>({
      query: () => '/student/notification-settings',
      transformResponse: (response: { data: NotificationSettings }) => response.data,
      providesTags: ['Viewer'],
    }),
    updateNotificationSettings: build.mutation<NotificationSettings, Partial<NotificationSettings>>({
      query: (body) => ({ url: '/student/notification-settings', method: 'PATCH', body }),
      transformResponse: (response: { data: NotificationSettings }) => response.data,
      async onQueryStarted(patch, { dispatch, queryFulfilled }) {
        // Optimistic toggle; rolled back if the server refuses.
        const undo = dispatch(pushApi.util.updateQueryData('notificationSettings', undefined, (draft) => Object.assign(draft, patch)));
        try {
          await queryFulfilled;
        } catch {
          undo.undo();
        }
      },
    }),
  }),
});

export const { usePushConfigQuery, useSubscribePushMutation, useNotificationSettingsQuery, useUpdateNotificationSettingsMutation } =
  pushApi;
