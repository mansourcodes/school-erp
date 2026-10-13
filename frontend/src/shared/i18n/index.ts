import ar from './ar.json';

type Params = Record<string, string | number>;

/**
 * Arabic-only UI copy (PLAN.md §4): every string lives in ar.json.
 * `t('login.attemptsLeft', { n: 2 })` replaces `{n}` in the message.
 */
export function t(key: string, params?: Params): string {
  const value = key.split('.').reduce<unknown>(
    (node, part) => (node && typeof node === 'object' ? (node as Record<string, unknown>)[part] : undefined),
    ar,
  );
  if (typeof value !== 'string') {
    if (import.meta.env.DEV) console.warn(`[i18n] missing key: ${key}`);
    return key;
  }
  return params ? value.replace(/\{(\w+)\}/g, (match, name: string) => String(params[name] ?? match)) : value;
}
