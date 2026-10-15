import { baseApi } from '@/shared/api';

export type PasswordOwner = 'student' | 'teacher';

export interface ChangePasswordBody {
  current_password: string;
  password: string;
  password_confirmation: string;
}

/** The viewer's own password (`PUT /student/password` or `/teacher/password`); the server signs their other devices out. */
export const changePasswordApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    changePassword: build.mutation<void, ChangePasswordBody & { role: PasswordOwner }>({
      query: ({ role, ...body }) => ({ url: `/${role}/password`, method: 'PUT', body }),
    }),
  }),
});

export const { useChangePasswordMutation } = changePasswordApi;
