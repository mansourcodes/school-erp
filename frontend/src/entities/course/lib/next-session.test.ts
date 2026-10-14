import type { CourseCurriculum } from '../model/types';
import { nextSession } from './next-session';

// Real data: the centre teaches on Friday (5) and Saturday (6).
const curricula: CourseCurriculum[] = [
  { id: 1, name: 'تجويد', teacher: 'الشيخ أحمد', teacher_photo_url: null, slots: [{ day: 5, start: '16:00', end: '17:00' }] },
  {
    id: 2,
    name: 'فقه',
    teacher: null,
    teacher_photo_url: null,
    slots: [
      { day: 5, start: '17:15', end: '18:15' },
      { day: 6, start: '09:00', end: '10:00' },
      { day: 3, start: null, end: null },
    ],
  },
];
const course = { curricula, start_date: '2026-09-04', end_date: '2026-12-26' };

// 2026-10-16 is a Friday.
const at = (iso: string, time: string) => new Date(`${iso}T${time}:00`);

describe('nextSession', () => {
  it('returns the earliest session later today', () => {
    expect(nextSession(course, at('2026-10-16', '10:00'))).toMatchObject({ dayOffset: 0, curriculum: 'تجويد', start: '16:00' });
  });

  it('keeps a session that is still running', () => {
    expect(nextSession(course, at('2026-10-16', '16:30'))).toMatchObject({ dayOffset: 0, curriculum: 'تجويد' });
  });

  it('moves to the next one after a session ends', () => {
    expect(nextSession(course, at('2026-10-16', '17:05'))).toMatchObject({ curriculum: 'فقه', start: '17:15' });
  });

  it('rolls over to the next teaching day', () => {
    expect(nextSession(course, at('2026-10-16', '19:00'))).toMatchObject({ date: '2026-10-17', dayOffset: 1, start: '09:00' });
    expect(nextSession(course, at('2026-10-12', '12:00'))).toMatchObject({ date: '2026-10-16', dayOffset: 4 });
  });

  it('skips slots without a time', () => {
    // Wednesday (3) only has a slot with no start time.
    expect(nextSession(course, at('2026-10-14', '08:00'))?.date).toBe('2026-10-16');
  });

  it('waits for the course to start and stops after it ends', () => {
    expect(nextSession(course, at('2026-08-30', '08:00'))?.date).toBe('2026-09-04');
    expect(nextSession(course, at('2026-12-26', '12:00'))).toBeNull();
  });

  it('returns null without any timed slot', () => {
    expect(nextSession({ ...course, curricula: [] }, at('2026-10-16', '08:00'))).toBeNull();
  });
});
