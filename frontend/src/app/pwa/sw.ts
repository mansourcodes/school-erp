/// <reference lib="webworker" />
// Service worker (PLAN.md §4 "Progressive Web App"): injectManifest, so push handlers can join in P3.4.
import { Queue } from 'workbox-background-sync';
import { ExpirationPlugin } from 'workbox-expiration';
import { cleanupOutdatedCaches, createHandlerBoundToURL, precacheAndRoute } from 'workbox-precaching';
import { NavigationRoute, registerRoute } from 'workbox-routing';
import { CacheFirst } from 'workbox-strategies';

declare let self: ServiceWorkerGlobalScope;

// The same build runs as /beta/sw.js (scope /beta/) during the beta and as /sw.js (scope /) once
// `portal.mode` is live (PortalAppController::serviceWorker). Files keep their /beta/ URLs in both.
const SCOPE = new URL(self.registration.scope).pathname;
const AT_ROOT = SCOPE === '/';
/** Server pages a root worker leaves alone (PortalAppController::SERVER_PREFIXES). */
const SERVER_PAGES = /^\/(admin|api|sanctum|storage|beta|pull|logout)(\/|$)/;
const isPanelPath = (pathname: string) => (AT_ROOT ? !SERVER_PAGES.test(pathname) : pathname.startsWith(SCOPE));

// App shell: index.html + hashed JS/CSS + icons, versioned per build (absolute /beta/ URLs, vite.config.ts).
precacheAndRoute(self.__WB_MANIFEST);
cleanupOutdatedCaches();

// Panel deep links open the shell, also offline (the app then shows its offline state).
registerRoute(
  new NavigationRoute(createHandlerBoundToURL('/beta/index.html'), {
    allowlist: [AT_ROOT ? /^\// : /^\/beta\//],
    denylist: AT_ROOT ? [SERVER_PAGES] : [],
  }),
);

// Fonts: cached on first use, not precached (≈600 KB across weights; immutable file names).
registerRoute(
  ({ request, url }) => request.destination === 'font' && url.origin === self.location.origin,
  new CacheFirst({
    cacheName: 'fonts',
    plugins: [new ExpirationPlugin({ maxEntries: 60, maxAgeSeconds: 365 * 24 * 60 * 60 })],
  }),
);

// Teacher roster saves (PUT, idempotent): when the network fails, keep the request and replay it when the
// connection returns, or when the worker next starts on browsers without Background Sync (PLAN.md §4, Q8).
// This is the only queued write.
type RosterEntry = NonNullable<Awaited<ReturnType<Queue['shiftRequest']>>>;

const rosterCode = (entry: RosterEntry) => decodeURIComponent(new URL(entry.request.url).pathname.split('/').pop() ?? '');

async function replayRosterSaves(queue: Queue) {
  // 419: the stored CSRF header is stale (the server session ended meanwhile; the remember cookie still
  // signs the teacher in). The worker cannot read the new token, so the saves stay queued and every open
  // page is offered them; the teacher's shell resends one and confirms (ROSTER_REPLAY_DONE), and only then is
  // it removed. The open window may be the login page or a stale tab, which ignore the offer.
  const stale: RosterEntry[] = [];
  try {
    let entry;
    while ((entry = await queue.shiftRequest())) {
      let response: Response;
      try {
        response = await fetch(entry.request.clone());
      } catch (error) {
        await queue.unshiftRequest(entry);
        throw error;
      }
      if (response.status === 419) {
        stale.push(entry);
        continue;
      }
      // Other 4xx (e.g. the roster changed) will not succeed later either: drop it.
      const code = rosterCode(entry);
      for (const client of await self.clients.matchAll({ type: 'window' })) {
        client.postMessage({ type: response.ok ? 'ROSTER_SYNCED' : 'ROSTER_REJECTED', code });
      }
    }
  } finally {
    // Back at the front, in their order.
    for (const entry of [...stale].reverse()) await queue.unshiftRequest(entry);
  }

  const clients = await self.clients.matchAll({ type: 'window' });
  for (const entry of stale) {
    const body = (await entry.request.clone().json()) as { states: Record<string, string> };
    for (const client of clients) client.postMessage({ type: 'ROSTER_REPLAY', code: rosterCode(entry), states: body.states });
  }
}

/** A page resent this session's save itself: it no longer waits here. */
async function forgetRosterSave(code: string) {
  const keep: RosterEntry[] = [];
  let entry;
  while ((entry = await rosterQueue.shiftRequest())) {
    if (rosterCode(entry) !== code) keep.push(entry);
  }
  for (const kept of keep) await rosterQueue.pushRequest(kept);
}

const rosterQueue = new Queue('roster-saves', {
  maxRetentionTime: 24 * 60,
  onSync: ({ queue }) => replayRosterSaves(queue),
});

registerRoute(
  ({ url, request }) => request.method === 'PUT' && url.pathname.startsWith('/api/v1/teacher/sessions/'),
  async ({ request }) => {
    try {
      return await fetch(request.clone());
    } catch (error) {
      await rosterQueue.pushRequest({ request });
      throw error;
    }
  },
  'PUT',
);

// Other /api/v1/* and /sanctum/* requests have no route on purpose: always the network, never stored here.
// Phones are often shared in a family; RTK Query's in-memory cache covers short offline gaps.

// Web Push (PLAN.md §4 "Push notifications"): show it, then open the screen it points to.
interface PushPayload {
  title?: string;
  body?: string;
  icon?: string;
  badge?: string;
  tag?: string;
  dir?: NotificationDirection;
  lang?: string;
  data?: { url?: string };
}

self.addEventListener('push', (event) => {
  let payload: PushPayload = {};
  try {
    payload = (event.data?.json() ?? {}) as PushPayload;
  } catch {
    payload = { body: event.data?.text() };
  }
  event.waitUntil(
    (async () => {
      await self.registration.showNotification(payload.title ?? '', {
        body: payload.body,
        icon: payload.icon ?? '/beta/pwa-192x192.png',
        badge: payload.badge ?? '/beta/pwa-64x64.png',
        // One notification per session / message: a repeat replaces it.
        tag: payload.tag,
        dir: payload.dir ?? 'rtl',
        lang: payload.lang ?? 'ar',
        data: payload.data ?? {},
      });
      // Open pages refresh the unread badge and lists.
      for (const client of await self.clients.matchAll({ type: 'window' })) {
        client.postMessage({ type: 'PUSH_RECEIVED', tag: payload.tag });
      }
    })(),
  );
});

self.addEventListener('notificationclick', (event) => {
  event.notification.close();
  const url = new URL((event.notification.data as { url?: string } | undefined)?.url ?? SCOPE, self.location.origin).href;
  event.waitUntil(
    (async () => {
      const windows = await self.clients.matchAll({ type: 'window', includeUncontrolled: true });
      const open = windows.find((client) => {
        const page = new URL(client.url);
        return page.origin === self.location.origin && isPanelPath(page.pathname);
      });
      if (open) {
        await open.focus();
        await open.navigate(url);
      } else {
        await self.clients.openWindow(url);
      }
    })(),
  );
});

self.addEventListener('message', (event) => {
  // "يتوفر تحديث جديد · تحديث": the page asks the waiting worker to take over.
  if (event.data?.type === 'SKIP_WAITING') void self.skipWaiting();
  // The teacher's shell resent a handed-over save (see replayRosterSaves).
  if (event.data?.type === 'ROSTER_REPLAY_DONE' && typeof event.data.code === 'string') {
    event.waitUntil(forgetRosterSave(event.data.code));
  }
  // The teacher's shell opened (e.g. after signing in again): offer it the saves still waiting.
  if (event.data?.type === 'ROSTER_FLUSH') event.waitUntil(replayRosterSaves(rosterQueue).catch(() => undefined));
  // Logout on a shared phone: forget queued saves of the previous teacher.
  if (event.data?.type === 'CLEAR_ROSTER_QUEUE') {
    event.waitUntil(
      (async () => {
        while (await rosterQueue.shiftRequest()) {
          // drained
        }
      })(),
    );
  }
});
