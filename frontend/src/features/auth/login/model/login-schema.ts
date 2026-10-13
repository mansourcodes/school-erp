// zod/mini: same validation, tree-shakeable (PLAN.md §4 mobile budget: ≤ 170 KB gzipped JS).
import { z } from 'zod/mini';
import { t } from '@/shared/i18n';

// Mirrors LoginRequest on the server: CPR digits:9, password ≤ 128.
export const loginSchema = z.object({
  cpr: z.string().check(z.regex(/^\d{9}$/, t('login.cprInvalid'))),
  password: z.string().check(z.minLength(1, t('login.passwordRequired')), z.maxLength(128)),
  website: z.optional(z.string()),
});

export type LoginValues = z.infer<typeof loginSchema>;
