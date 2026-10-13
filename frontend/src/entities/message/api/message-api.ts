import { baseApi } from '@/shared/api';
import type { InboxMessage, Page, SentMessage } from '../model/types';

export const messageApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    inbox: build.query<Page<InboxMessage>, number | void>({
      query: (page) => ({ url: '/student/messages', params: page ? { page } : undefined }),
      providesTags: ['Inbox'],
    }),
    sentMessages: build.query<Page<SentMessage>, number | void>({
      query: (page) => ({ url: '/teacher/messages', params: page ? { page } : undefined }),
      providesTags: ['Sent'],
    }),
  }),
});

export const { useInboxQuery, useSentMessagesQuery } = messageApi;
