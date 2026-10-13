import type { AttendanceState, Session } from '../model/types';
import { summarize } from './summarize';

const session = (state: AttendanceState): Session => ({
  date: '2026-09-11',
  start: '16:00',
  end: '17:00',
  curriculum_id: 1,
  curriculum: 'تجويد',
  teacher: null,
  state,
  other_classroom: false,
});

describe('summarize', () => {
  it('matches the server definitions (unrecorded excluded, late counts as attended)', () => {
    const stats = summarize(
      (['present', 'present', 'late', 'late_excused', 'absent', 'absent_excused', 'unrecorded'] as AttendanceState[]).map(session),
    );

    expect(stats.recorded).toBe(6);
    expect(stats.attended).toBe(4);
    expect(stats.rate).toBe(66.7);
    expect(stats.punctual).toBe(33.3);
    expect(stats.counts.unrecorded).toBe(1);
  });

  it('has no rate before anything is recorded', () => {
    expect(summarize([session('unrecorded')])).toMatchObject({ recorded: 0, rate: null, punctual: null });
    expect(summarize([]).rate).toBeNull();
  });
});
