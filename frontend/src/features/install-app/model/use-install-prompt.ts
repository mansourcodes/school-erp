import { useEffect, useState } from 'react';

interface BeforeInstallPromptEvent extends Event {
  prompt: () => Promise<void>;
  userChoice: Promise<{ outcome: 'accepted' | 'dismissed' }>;
}

const VISITS_KEY = 'gh-visits';
const DISMISSED_KEY = 'gh-install-dismissed-until';
const COUNTED_KEY = 'gh-visit-counted';
export const JUST_LOGGED_IN_KEY = 'gh-just-logged-in';
const THIRTY_DAYS = 30 * 24 * 60 * 60 * 1000;

function read(storage: Storage | undefined, key: string): string | null {
  try {
    return storage?.getItem(key) ?? null;
  } catch {
    return null;
  }
}

function write(storage: Storage | undefined, key: string, value: string) {
  try {
    storage?.setItem(key, value);
  } catch {
    // Private mode: the banner may show again next visit.
  }
}

function isStandalone(): boolean {
  return (
    window.matchMedia?.('(display-mode: standalone)').matches === true ||
    (navigator as Navigator & { standalone?: boolean }).standalone === true
  );
}

/**
 * PLAN.md §4 "Install prompt": Android/Chrome uses `beforeinstallprompt`; iOS Safari has no API,
 * so it gets a how-to sheet. Shown from the second visit or right after a login, hidden for
 * 30 days once dismissed, never inside the installed app.
 */
export function useInstallPrompt() {
  const [deferred, setDeferred] = useState<BeforeInstallPromptEvent | null>(null);
  const [hidden, setHidden] = useState(() => Number(read(localStorage, DISMISSED_KEY) ?? 0) > Date.now());

  const [visits] = useState(() => {
    let count = Number(read(localStorage, VISITS_KEY) ?? 0);
    if (!read(sessionStorage, COUNTED_KEY)) {
      count += 1;
      write(localStorage, VISITS_KEY, String(count));
      write(sessionStorage, COUNTED_KEY, '1');
    }
    return count;
  });
  const justLoggedIn = read(sessionStorage, JUST_LOGGED_IN_KEY) === '1';

  useEffect(() => {
    const capture = (event: Event) => {
      event.preventDefault();
      setDeferred(event as BeforeInstallPromptEvent);
    };
    window.addEventListener('beforeinstallprompt', capture);
    return () => window.removeEventListener('beforeinstallprompt', capture);
  }, []);

  const ios = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const eligible = !hidden && !isStandalone() && (visits >= 2 || justLoggedIn);

  return {
    /** 'android' = native prompt available; 'ios' = show the steps sheet. */
    mode: eligible ? (deferred ? 'android' : ios ? 'ios' : null) : null,
    install: async () => {
      if (!deferred) return;
      await deferred.prompt();
      await deferred.userChoice;
      setDeferred(null);
    },
    dismiss: () => {
      write(localStorage, DISMISSED_KEY, String(Date.now() + THIRTY_DAYS));
      setHidden(true);
    },
  } as const;
}
