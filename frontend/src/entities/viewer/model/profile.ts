/** GET /api/v1/student/profile (PLAN.md §3.6): read-only, whitelisted fields. */
export interface Profile {
  name: string;
  /** 9 digits */
  cpr: string | null;
  dob: string | null;
  age: number | null;
  degree: string | null;
  mobile: string | null;
  mobile2: string | null;
  email: string | null;
  address: string | null;
  student_id: string;
  registration_at: string | null;
  iqraa_id_account: string | null;
  photo_url: string | null;
}

/** `040512345` → `•••••2345` (shown until the student taps reveal). */
export function maskCpr(cpr: string): string {
  return `${'•'.repeat(Math.max(0, cpr.length - 4))}${cpr.slice(-4)}`;
}
