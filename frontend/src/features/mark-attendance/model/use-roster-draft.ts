import { useCallback, useMemo, useState } from 'react';
import type { RosterState, RosterStudent } from '@/entities/class-session';

export type Draft = Record<number, RosterState | null>;

const fromStudents = (students: RosterStudent[]): Draft =>
  Object.fromEntries(students.map((s) => [s.id, s.state])) as Draft;

/**
 * The unsaved roster (PLAN.md §8 roster): per-student state, quick "all present / all absent",
 * undo back to the last saved states. Saving needs every student set and something changed.
 */
export function useRosterDraft(students: RosterStudent[]) {
  const saved = useMemo(() => fromStudents(students), [students]);
  const [draft, setDraft] = useState<Draft>(saved);
  const [base, setBase] = useState(saved);

  // A refetch (e.g. after saving) becomes the new baseline.
  if (base !== saved) {
    setBase(saved);
    setDraft(saved);
  }

  const set = useCallback((id: number, state: RosterState) => setDraft((d) => ({ ...d, [id]: state })), []);
  const setAll = useCallback(
    (state: RosterState) => setDraft(Object.fromEntries(students.map((s) => [s.id, state])) as Draft),
    [students],
  );
  const undo = useCallback(() => setDraft(saved), [saved]);

  const changed = students.filter((s) => draft[s.id] !== saved[s.id]).map((s) => s.id);
  const setCount = students.filter((s) => draft[s.id]).length;

  return {
    draft,
    set,
    setAll,
    undo,
    changed: new Set(changed),
    dirty: changed.length > 0,
    setCount,
    complete: setCount === students.length,
    /** Only when complete: the body for PUT. */
    payload: () => Object.fromEntries(students.map((s) => [s.id, draft[s.id] as RosterState])) as Record<number, RosterState>,
  };
}
