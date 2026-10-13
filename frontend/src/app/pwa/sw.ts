/// <reference lib="webworker" />
// Service worker (PLAN.md §4 "Progressive Web App"): injectManifest, so push handlers can join in P3.4.
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

// /api/v1/* and /sanctum/* have no route on purpose: always the network, never stored here.
// Phones are often shared in a family; RTK Query's in-memory cache covers short offline gaps.

self.addEventListener('message', (event) => {
  // "يتوفر تحديث جديد · تحديث": the page asks the waiting worker to take over.
  if (event.data?.type === 'SKIP_WAITING') void self.skipWaiting();
});
