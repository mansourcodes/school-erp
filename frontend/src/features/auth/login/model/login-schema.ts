// zod/mini: same validation, tree-shakeable (PLAN.md §4 mobile budget: ≤ 170 KB gzipped JS).
import { z } from 'zod/mini';
import { t } from '@/shared/i18n';

const EMAIL = /^[^@\s]+@[^@\s]+\.[^@\s]+$/;

// Mirrors LoginRequest on the server: student CPR digits:9, teacher email, password ≤ 128.
export const loginSchema = z
  .object({
    role: z.enum(['student', 'teacher']),
    cpr: z.string(),
    email: z.string(),
    password: z.string().check(z.minLength(1, t('login.passwordRequired')), z.maxLength(128)),
    website: z.optional(z.string()),
  })
  .check(
    z.refine((v) => v.role !== 'student' || /^\d{9}$/.test(v.cpr), { message: t('login.cprInvalid'), path: ['cpr'] }),
    z.refine((v) => v.role !== 'teacher' || EMAIL.test(v.email.trim()), { message: t('login.emailInvalid'), path: ['email'] }),
  );

export type LoginValues = z.infer<typeof loginSchema>;
export type LoginRole = LoginValues['role'];
