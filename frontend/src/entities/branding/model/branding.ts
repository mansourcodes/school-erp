/** The panel's texts, Backpack Settings `portal.*` (app/Support/PortalBranding.php). Images stay static files. */
export interface Branding {
  name: string;
  short_name: string;
  centre: string;
  description: string;
}

/** GET /api/v1/theme: the theme (features/portal-theme) and the branding texts. */
export interface PortalInfo extends Partial<Branding> {
  theme?: unknown;
  /** The panel's page root: '/beta/', or '/' once `portal.mode` is live (app/pwa/use-app-base). */
  base?: string;
}

const STORAGE_KEY = 'gh-branding';

const EMPTY: Branding = { name: '', short_name: '', centre: '', description: '' };

export function toBranding(info: PortalInfo | undefined): Branding | null {
  if (!info || typeof info.name !== 'string') return null;
  return {
    name: info.name,
    short_name: info.short_name ?? '',
    centre: info.centre ?? '',
    description: info.description ?? '',
  };
}

/** The last texts the server sent, so an offline start still shows the names. */
export function readStoredBranding(): Branding {
  try {
    return { ...EMPTY, ...(JSON.parse(localStorage.getItem(STORAGE_KEY) ?? '{}') as Partial<Branding>) };
  } catch {
    return EMPTY;
  }
}

export function storeBranding(branding: Branding) {
  try {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(branding));
  } catch {
    // Private mode: the texts load again next time.
  }
}
