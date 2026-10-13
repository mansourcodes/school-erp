/// <reference lib="webworker" />
// Service worker (PLAN.md §4 "Progressive Web App"): injectManifest, so push handlers can join in P3.4.
import { Queue } from 'workbox-background-sync';
import { ExpirationPlugin } from 'workbox-expiration';
import { cleanupOutdatedCaches, createHandlerBoundToURL, precacheAndRoute } from 'workbox-precaching';
import { NavigationRoute, registerRoute } from 'workbox-routing';
import { CacheFirst } from 'workbox-strategies';

declare let self: ServiceWorkerGlobalScope;

// App shell: index.html + hashed JS/CSS + icons, versioned per build.
precacheAndRoute(self.__WB_MANIFEST);
cleanupOutdatedCaches();

// Deep links under /beta/ open the shell, also offline (the app then shows its offline state).
registerRoute(new NavigationRoute(createHandlerBoundToURL('/beta/index.html'), { allowlist: [/^\/beta\//] }));

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
const rosterQueue = new Queue('roster-saves', {
  maxRetentionTime: 24 * 60,
  onSync: async ({ queue }) => {
    let entry;
    while ((entry = await queue.shiftRequest())) {
      try {
        const response = await fetch(entry.request.clone());
        const code = decodeURIComponent(new URL(entry.request.url).pathname.split('/').pop() ?? '');
        // 4xx (e.g. the roster changed or the session expired) will not succeed later either: drop it.
        const clients = await self.clients.matchAll({ type: 'window' });
        for (const client of clients) {
          client.postMessage({ type: response.ok ? 'ROSTER_SYNCED' : 'ROSTER_REJECTED', code });
        }
      } catch (error) {
        await queue.unshiftRequest(entry);
        throw error;
      }
    }
  },
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

self.addEventListener('message', (event) => {
  // "يتوفر تحديث جديد · تحديث": the page asks the waiting worker to take over.
  if (event.data?.type === 'SKIP_WAITING') void self.skipWaiting();
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
