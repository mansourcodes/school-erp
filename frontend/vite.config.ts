import { fileURLToPath, URL } from 'node:url';
import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import { loadEnv } from 'vite';
import { VitePWA } from 'vite-plugin-pwa';
import { defineConfig } from 'vitest/config';

// The SPA is served by Laravel at /beta (PLAN.md §4 "Beta rollout").
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
        },
        manifest: {
          name: 'بوابة الغدير',
          short_name: 'الغدير',
          description: 'بوابة الطالب — جمعية الغدير لعلوم القرآن',
          lang: 'ar',
          dir: 'rtl',
          start_url: '/beta/',
          scope: '/beta/',
          display: 'standalone',
          display_override: ['standalone', 'minimal-ui'],
          theme_color: '#ffffff',
          background_color: '#f5f3ec',
          icons: [
            { src: 'pwa-64x64.png', sizes: '64x64', type: 'image/png' },
            { src: 'pwa-192x192.png', sizes: '192x192', type: 'image/png' },
            { src: 'pwa-512x512.png', sizes: '512x512', type: 'image/png' },
            { src: 'maskable-icon-512x512.png', sizes: '512x512', type: 'image/png', purpose: 'maskable' },
          ],
          shortcuts: [
            { name: 'الدورة الحالية', url: '/beta/', icons: [{ src: 'pwa-192x192.png', sizes: '192x192' }] },
            { name: 'سجل الحضور', url: '/beta/attendance', icons: [{ src: 'pwa-192x192.png', sizes: '192x192' }] },
          ],
        },
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
