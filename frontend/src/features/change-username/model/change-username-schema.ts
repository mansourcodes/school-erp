import { z } from 'zod/mini';
import { t } from '@/shared/i18n';

export const USERNAME_MAX_LENGTH = 50;

// Mirrors TeacherUsernameController: User::usernameRules (3–50 of a-z 0-9 . _ -, stored lowercase, unique)
// plus the current password.
const USERNAME = /^[a-z0-9._-]+$/i;

export const changeUsernameSchema = z.object({
  username: z
    .string()
    .check(
      z.trim(),
      z.minLength(1, t('changeUsername.required')),
      z.minLength(3, t('changeUsername.min')),
      z.maxLength(USERNAME_MAX_LENGTH, t('changeUsername.max')),
      z.regex(USERNAME, t('changeUsername.format')),
    ),
  current_password: z.string().check(z.minLength(1, t('changePassword.currentRequired')), z.maxLength(128)),
});

export type ChangeUsernameValues = z.infer<typeof changeUsernameSchema>;
