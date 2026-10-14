import { applyTheme, readStoredTheme } from './themes';

describe('portal themes', () => {
  it('applies the theme and remembers it for the next first paint', () => {
    applyTheme('girls');
    expect(document.documentElement.getAttribute('data-theme')).toBe('girls');
    expect(readStoredTheme()).toBe('girls');
  });

  it('falls back to the default theme for old or unknown saved values', () => {
    localStorage.setItem('gh-theme', 'dark');
    expect(readStoredTheme()).toBe('ghadeer');
    localStorage.setItem('gh-theme', 'toString');
    expect(readStoredTheme()).toBe('ghadeer');
  });
});
