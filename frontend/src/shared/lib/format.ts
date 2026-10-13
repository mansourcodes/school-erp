// Western digits everywhere (PLAN.md §2 Typography); Hijri as secondary text.
const LOCALE = 'ar-u-nu-latn';
const HIJRI_LOCALE = 'ar-SA-u-ca-islamic-umalqura-nu-latn';

/** Parses `YYYY-MM-DD` as a local date (no UTC shift). */
export function parseIsoDate(iso: string): Date {
  const [y, m, d] = iso.split('-').map(Number);
  return new Date(y, m - 1, d);
}

export function fmtDate(
  iso: string,
  options: Intl.DateTimeFormatOptions = { day: 'numeric', month: 'long', year: 'numeric' },
): string {
  return new Intl.DateTimeFormat(LOCALE, options).format(parseIsoDate(iso));
}

export function fmtHijri(iso: string): string {
  try {
    return new Intl.DateTimeFormat(HIJRI_LOCALE, { day: 'numeric', month: 'long', year: 'numeric' }).format(
      parseIsoDate(iso),
    );
  } catch {
    return '';
  }
}

/** `16:05` → `4:05 م` */
export function fmtTime(hm: string): string {
  const [h, m] = hm.split(':');
  const hour = Number(h);
  return `${hour % 12 || 12}:${m} ${hour >= 12 ? 'م' : 'ص'}`;
}

/** Bahraini dinar, 3 decimals: `45.000 د.ب` */
export function money(amount: number): string {
  return `${amount.toFixed(3)} د.ب`;
}

export function pct(value: number): string {
  return `${(Math.round(value * 10) / 10).toLocaleString('en', { maximumFractionDigits: 1 })}%`;
}

/** Seconds → `mm:ss` (lockout countdown). */
export function fmtCountdown(totalSeconds: number): string {
  const s = Math.max(0, Math.ceil(totalSeconds));
  return `${String(Math.floor(s / 60)).padStart(2, '0')}:${String(s % 60).padStart(2, '0')}`;
}
