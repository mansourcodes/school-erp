/** The student's Iqraa platform account as a small labelled badge ("iqraa-1234567"); nothing when empty. */
export function IqraaBadge({ account }: { account: string | null | undefined }) {
  if (!account) return null;
  return <span className="badge badge-soft badge-primary badge-xs ltr num font-normal">iqraa-{account}</span>;
}
