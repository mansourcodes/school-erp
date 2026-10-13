// zod/mini: same validation, tree-shakeable (PLAN.md §4 mobile budget: ≤ 170 KB gzipped JS).
import { z } from 'zod/mini';
import { t } from '@/shared/i18n';

const EMAIL = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

/** Student CPR: digits only, 1–12 long. */
export const CPR_MAX_LENGTH = 12;
const CPR = /^\d{1,12}$/;

// Mirrors LoginRequest on the server: student CPR digits_between:1,12, teacher email, password ≤ 128.
export const loginSchema = z
  .object({
    role: z.enum(['student', 'teacher']),
    cpr: z.string(),
    email: z.string(),
    password: z.string().check(z.minLength(1, t('login.passwordRequired')), z.maxLength(128)),
    website: z.optional(z.string()),
  })
  .check(
    z.refine((v) => v.role !== 'student' || CPR.test(v.cpr), { message: t('login.cprInvalid'), path: ['cpr'] }),
    z.refine((v) => v.role !== 'teacher' || EMAIL.test(v.email.trim()), { message: t('login.emailInvalid'), path: ['email'] }),
  );

export type LoginValues = z.infer<typeof loginSchema>;
export type LoginRole = LoginValues['role'];
