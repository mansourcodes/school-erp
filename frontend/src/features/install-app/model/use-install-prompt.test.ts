import { act, renderHook } from '@testing-library/react';
import { JUST_LOGGED_IN_KEY, useInstallPrompt } from './use-install-prompt';

function fireInstallPrompt() {
  const event = Object.assign(new Event('beforeinstallprompt'), {
    prompt: vi.fn(() => Promise.resolve()),
    userChoice: Promise.resolve({ outcome: 'accepted' as const }),
  });
  act(() => {
    window.dispatchEvent(event);
  });
  return event;
}

describe('useInstallPrompt', () => {
  beforeEach(() => sessionStorage.clear());

  it('stays hidden on the first visit', () => {
    const { result } = renderHook(() => useInstallPrompt());
    fireInstallPrompt();
    expect(result.current.mode).toBeNull();
  });

  it('offers the native prompt from the second visit', async () => {
    localStorage.setItem('gh-visits', '1');
    const { result } = renderHook(() => useInstallPrompt());
    const event = fireInstallPrompt();

    expect(result.current.mode).toBe('android');
    await act(() => result.current.install());
    expect(event.prompt).toHaveBeenCalledOnce();
    expect(result.current.mode).toBeNull();
  });

  it('shows right after a login, and hides for 30 days once dismissed', () => {
    sessionStorage.setItem(JUST_LOGGED_IN_KEY, '1');
    const { result } = renderHook(() => useInstallPrompt());
    fireInstallPrompt();
    expect(result.current.mode).toBe('android');

    act(() => result.current.dismiss());
    expect(result.current.mode).toBeNull();
    const until = Number(localStorage.getItem('gh-install-dismissed-until'));
    expect(until - Date.now()).toBeGreaterThan(29 * 24 * 3600 * 1000);
  });

  it('uses the how-to sheet on iOS, where there is no install API', () => {
    localStorage.setItem('gh-visits', '3');
    const ua = vi.spyOn(navigator, 'userAgent', 'get').mockReturnValue('Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X)');
    const { result } = renderHook(() => useInstallPrompt());
    expect(result.current.mode).toBe('ios');
    ua.mockRestore();
  });
});
