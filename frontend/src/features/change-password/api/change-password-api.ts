import { baseApi } from '@/shared/api';

export interface ChangePasswordBody {
  current_password: string;
  password: string;
  password_confirmation: string;
}

/** The teacher's own password; the server signs their other devices out. */
export const changePasswordApi = baseApi.injectEndpoints({
  endpoints: (build) => ({
    changeTeacherPassword: build.mutation<void, ChangePasswordBody>({
      query: (body) => ({ url: '/teacher/password', method: 'PUT', body }),
    }),
  }),
});

export const { useChangeTeacherPasswordMutation } = changePasswordApi;
