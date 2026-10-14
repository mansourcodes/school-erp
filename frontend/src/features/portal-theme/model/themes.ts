/** Same list as app/Support/PortalTheme.php; value = browser bar colour (the top bar's base-100). */
const THEME_COLORS = {
  ghadeer: '#ffffff',
  girls: '#fffcfb',
  cupcake: '#faf7f5',
  autumn: '#f1f1f1',
  wireframe: '#ffffff',
} as const;

export type PortalTheme = keyof typeof THEME_COLORS;

export const DEFAULT_THEME: PortalTheme = 'ghadeer';

/** Same key as public/theme-boot.js, which applies the last known theme before first paint. */
export const THEME_STORAGE_KEY = 'gh-theme';

export function isPortalTheme(value: unknown): value is PortalTheme {
  return typeof value === 'string' && Object.hasOwn(THEME_COLORS, value);
}

export function readStoredTheme(): PortalTheme {
  try {
    const value = localStorage.getItem(THEME_STORAGE_KEY);
    return isPortalTheme(value) ? value : DEFAULT_THEME;
  } catch {
    return DEFAULT_THEME;
  }
}

/** Applies the theme and remembers it on this device for the next first paint. */
export function applyTheme(theme: PortalTheme) {
  document.documentElement.setAttribute('data-theme', theme);
  document.querySelector('meta[name="theme-color"]')?.setAttribute('content', THEME_COLORS[theme]);
  try {
    localStorage.setItem(THEME_STORAGE_KEY, theme);
  } catch {
    // Private mode: the server's theme is fetched again on the next visit.
  }
}
