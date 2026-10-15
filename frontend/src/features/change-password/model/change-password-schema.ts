import { z } from 'zod/mini';
import { t } from '@/shared/i18n';

// Mirrors TeacherPasswordController: new password 8–128, confirmed, different from the current one.
export const changePasswordSchema = z
  .object({
    current_password: z.string().check(z.minLength(1, t('changePassword.currentRequired')), z.maxLength(128)),
    password: z
      .string()
      .check(z.minLength(8, t('changePassword.min')), z.maxLength(128, t('changePassword.max'))),
    password_confirmation: z.string(),
  })
  .check(
    z.refine((v) => v.password === v.password_confirmation, {
      message: t('changePassword.mismatch'),
      path: ['password_confirmation'],
    }),
    z.refine((v) => v.password === '' || v.password !== v.current_password, {
      message: t('changePassword.different'),
      path: ['password'],
    }),
  );

export type ChangePasswordValues = z.infer<typeof changePasswordSchema>;
