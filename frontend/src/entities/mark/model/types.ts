export interface Mark {
  key: string;
  label: string;
  max: number | null;
  /** null while not recorded yet */
  mark: number | null;
}

/** Sum of the recorded marks and of their maxima. */
export function marksTotals(marks: Mark[]): { sum: number; max: number } {
  return marks.reduce(
    (acc, m) => ({ sum: acc.sum + (m.mark ?? 0), max: acc.max + (m.max ?? 0) }),
    { sum: 0, max: 0 },
  );
}
