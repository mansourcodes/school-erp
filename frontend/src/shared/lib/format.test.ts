import { fmtCountdown, fmtTime, money, parseIsoDate, pct } from './format';

describe('format', () => {
  it('parses ISO dates as local dates', () => {
    const d = parseIsoDate('2026-10-12');
    expect([d.getFullYear(), d.getMonth(), d.getDate()]).toEqual([2026, 9, 12]);
  });

  it('formats 24h times with Arabic AM/PM', () => {
    expect(fmtTime('16:05')).toBe('4:05 م');
    expect(fmtTime('00:30')).toBe('12:30 ص');
    expect(fmtTime('12:00')).toBe('12:00 م');
  });

  it('formats dinars with 3 decimals', () => {
    expect(money(45)).toBe('45.000 د.ب');
  });

  it('rounds percentages to one decimal', () => {
    expect(pct(93.456)).toBe('93.5%');
    expect(pct(80)).toBe('80%');
  });

  it('formats countdowns as mm:ss', () => {
    expect(fmtCountdown(900)).toBe('15:00');
    expect(fmtCountdown(59.2)).toBe('01:00');
    expect(fmtCountdown(-3)).toBe('00:00');
  });
});
