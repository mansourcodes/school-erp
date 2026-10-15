import { fileURLToPath, URL } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { loadEnv } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import { defineConfig } from 'vitest/config';

// The SPA is built into public/beta and its assets keep /beta/ URLs. Laravel serves the pages at /beta
// during the beta and at the root once `portal.mode` is live (PLAN.md §4); the router base is set at runtime.
export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '');
  const apiTarget = env.VITE_DEV_API_TARGET || 'http://localhost';

  return {
    base: '/beta/',
    plugins: [
      react(),
      tailwindcss(),
      // PLAN.md §4 "Progressive Web App": installable, app shell precached, updates on the user's tap.
      VitePWA({
        strategies: 'injectManifest',
        srcDir: 'src/app/pwa',
        filename: 'sw.ts',
        registerType: 'prompt',
        injectRegister: false,
        injectManifest: {
          // Fonts are cached on first use by the worker, not precached.
          globPatterns: ['**/*.{js,css,html,svg,png,ico}'],
          // Absolute URLs: once live the same worker is served as /sw.js (PortalAppController::serviceWorker),
          // where relative ones would resolve against / instead of /beta/.
          modifyURLPrefix: { '': '/beta/' },
        },
        // Served by Laravel from Backpack Settings (PortalAppController::manifest); linked in index.html.
        manifest: false,
        devOptions: { enabled: false },
      }),
    ],
    resolve: {
      alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) },
    },
    build: {
      outDir: '../public/beta',
      emptyOutDir: true,
    },
    server: {
      // Same-origin in dev too: the browser only talks to Vite, which proxies to Laravel.
      proxy: {
        '/api': { target: apiTarget },
        '/sanctum': { target: apiTarget },
        // Built from Backpack Settings by Laravel.
        '/beta/manifest.webmanifest': { target: apiTarget },
      },
    },
    test: {
      globals: true,
      environment: 'jsdom',
      setupFiles: ['./test/setup.ts'],
      css: false,
    },
  };
});
