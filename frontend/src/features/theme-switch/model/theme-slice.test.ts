import { themeModeCycled, themeModeSet, themeReducer } from './theme-slice';

describe('theme slice', () => {
  it('cycles system → light → dark → system', () => {
    let state = themeReducer({ mode: 'system' }, themeModeCycled());
    expect(state.mode).toBe('light');
    state = themeReducer(state, themeModeCycled());
    expect(state.mode).toBe('dark');
    state = themeReducer(state, themeModeCycled());
    expect(state.mode).toBe('system');
  });

  it('reads the saved mode on start', () => {
    localStorage.setItem('gh-theme', 'dark');
    expect(themeReducer(undefined, { type: 'init' }).mode).toBe('dark');
  });

  it('ignores unknown saved values', () => {
    localStorage.setItem('gh-theme', 'neon');
    expect(themeReducer(undefined, themeModeSet('light')).mode).toBe('light');
    expect(themeReducer(undefined, { type: 'init' }).mode).toBe('system');
  });
});
