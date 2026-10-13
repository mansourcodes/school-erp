import { createSlice, type PayloadAction } from '@reduxjs/toolkit';

export type ThemeMode = 'system' | 'light' | 'dark';

export const THEME_MODES: ThemeMode[] = ['system', 'light', 'dark'];

/** Same key as public/theme-boot.js, which applies it before first paint. */
export const THEME_STORAGE_KEY = 'gh-theme';

function readStoredMode(): ThemeMode {
  try {
    const value = localStorage.getItem(THEME_STORAGE_KEY);
    return value === 'light' || value === 'dark' ? value : 'system';
  } catch {
    return 'system';
  }
}

const themeSlice = createSlice({
  name: 'theme',
  initialState: () => ({ mode: readStoredMode() }),
  reducers: {
    themeModeSet: (state, action: PayloadAction<ThemeMode>) => {
      state.mode = action.payload;
    },
    themeModeCycled: (state) => {
      state.mode = THEME_MODES[(THEME_MODES.indexOf(state.mode) + 1) % THEME_MODES.length];
    },
  },
});

export const { themeModeSet, themeModeCycled } = themeSlice.actions;
export const themeReducer = themeSlice.reducer;
export const selectThemeMode = (state: RootState) => state.theme.mode;
