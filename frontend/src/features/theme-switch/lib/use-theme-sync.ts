import { useEffect } from 'react';
import { useAppSelector } from '@/shared/lib';
import { selectThemeMode, THEME_STORAGE_KEY } from '../model/theme-slice';

function applyTheme(dark: boolean) {
  document.documentElement.setAttribute('data-theme', dark ? 'ghadeer-night' : 'ghadeer');
  // The browser bar follows the top bar colour.
  document.querySelector('meta[name="theme-color"]')?.setAttribute('content', dark ? '#14201e' : '#ffffff');
}

/** Persists the theme per device and applies it, following the OS setting in "system" mode. */
export function useThemeSync() {
  const mode = useAppSelector(selectThemeMode);

  useEffect(() => {
    try {
      localStorage.setItem(THEME_STORAGE_KEY, mode);
    } catch {
      // Private mode: the choice lasts for this visit only.
    }

    const query = window.matchMedia?.('(prefers-color-scheme: dark)');
    const apply = () => applyTheme(mode === 'dark' || (mode === 'system' && !!query?.matches));
    apply();

    if (mode !== 'system' || !query) return;
    query.addEventListener('change', apply);
    return () => query.removeEventListener('change', apply);
  }, [mode]);
}
