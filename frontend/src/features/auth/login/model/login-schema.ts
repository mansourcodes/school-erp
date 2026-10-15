// zod/mini: same validation, tree-shakeable (PLAN.md §4 mobile budget: ≤ 170 KB gzipped JS).
import { z } from 'zod/mini';
import { t } from '@/shared/i18n';

/** Student CPR: digits only, 1–12 long. */
export const CPR_MAX_LENGTH = 12;
const CPR = /^\d{1,12}$/;

export const USERNAME_MAX_LENGTH = 50;

// Mirrors LoginRequest on the server: student CPR digits_between:1,12, teacher username ≤ 50, password ≤ 128.
// The username's format is not checked here either: a malformed one fails like a wrong one.
export const loginSchema = z
  .object({
    role: z.enum(['student', 'teacher']),
    cpr: z.string(),
    username: z.string(),
    password: z.string().check(z.minLength(1, t('login.passwordRequired')), z.maxLength(128)),
    website: z.optional(z.string()),
  })
  .check(
    z.refine((v) => v.role !== 'student' || CPR.test(v.cpr), { message: t('login.cprInvalid'), path: ['cpr'] }),
    z.refine((v) => v.role !== 'teacher' || v.username.trim() !== '', { message: t('login.usernameRequired'), path: ['username'] }),
    z.refine((v) => v.role !== 'teacher' || v.username.trim().length <= USERNAME_MAX_LENGTH, {
      message: t('login.usernameInvalid'),
      path: ['username'],
    }),
  );

export type LoginValues = z.infer<typeof loginSchema>;
export type LoginRole = LoginValues['role'];
