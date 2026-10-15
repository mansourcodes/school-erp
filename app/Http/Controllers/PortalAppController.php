<?php

namespace App\Http\Controllers;

use App\Support\PortalBranding;
use App\Support\PortalMode;
use Illuminate\Http\Request;

/**
 * The new panel (React SPA, built into public/beta). Hashed assets and sw.js are static files; every
 * other panel path is a client-side route, so it gets the app shell. The texts (title, manifest) come
 * from Backpack Settings (PortalBranding); images stay static files.
 *
 * Pages live under /beta until `portal.mode` is live, then at the root (/beta redirects there, for
 * bookmarks); PortalMode::appPath() builds both. Assets keep /beta/ in every mode.
 */
class PortalAppController extends Controller
{
    /** Top-level paths that are never panel pages, even when live. */
    const SERVER_PREFIXES = ['admin', 'api', 'sanctum', 'storage', 'beta', 'pull', 'logout'];

    /** GET /beta/{path?} */
    public function beta(Request $request, $path = null)
    {
        if (PortalMode::is(PortalMode::LIVE)) {
            $query = $request->getQueryString();

            return redirect(PortalMode::appPath($path ?? '/') . ($query ? '?' . $query : ''));
        }

        return $this->shell();
    }

    /** Unmatched GET paths: panel pages at the root once live, a 404 otherwise. */
    public function fallback(Request $request)
    {
        abort_unless(
            $request->isMethod('GET')
                && PortalMode::is(PortalMode::LIVE)
                && !in_array($request->segment(1), self::SERVER_PREFIXES, true),
            404
        );

        return $this->shell();
    }

    /** GET /sw.js: the worker with scope / once live, so it controls the root pages. */
    public function serviceWorker()
    {
        $file = public_path('beta/sw.js');
        abort_unless(PortalMode::is(PortalMode::LIVE) && is_file($file), 404);

        return response(file_get_contents($file), 200, [
            'Content-Type' => 'application/javascript; charset=UTF-8',
            'Cache-Control' => 'no-cache',
        ]);
    }

    /** The app shell (index.html) with the panel's texts. */
    public function shell()
    {
        $index = public_path('beta/index.html');
        abort_unless(is_file($index), 404);

        $html = str_replace(
            ['<title></title>', '<meta name="apple-mobile-web-app-title" content="" />'],
            [
                '<title>' . e(PortalBranding::get('name')) . '</title>',
                '<meta name="apple-mobile-web-app-title" content="' . e(PortalBranding::get('short_name')) . '" />',
            ],
            file_get_contents($index)
        );

        return response($html, 200, ['Cache-Control' => 'no-cache', 'Content-Type' => 'text/html; charset=UTF-8']);
    }

    /** GET /beta/manifest.webmanifest (PLAN.md §4 "Progressive Web App"). */
    public function manifest()
    {
        $icon = function ($size, $purpose = null) {
            return array_filter([
                'src' => '/beta/' . ($purpose ? 'maskable-icon-' : 'pwa-') . $size . '.png',
                'sizes' => $size,
                'type' => 'image/png',
                'purpose' => $purpose,
            ]);
        };

        return response()->json([
            'name' => PortalBranding::get('name'),
            'short_name' => PortalBranding::get('short_name'),
            'description' => PortalBranding::get('description'),
            'lang' => 'ar',
            'dir' => 'rtl',
            'start_url' => PortalMode::appPath('/'),
            'scope' => PortalMode::appPath('/'),
            'display' => 'standalone',
            'display_override' => ['standalone', 'minimal-ui'],
            'theme_color' => '#ffffff',
            'background_color' => '#f5f3ec',
            'icons' => [$icon('64x64'), $icon('192x192'), $icon('512x512'), $icon('512x512', 'maskable')],
            'shortcuts' => [
                ['name' => 'الدورة الحالية', 'url' => PortalMode::appPath('/'), 'icons' => [['src' => '/beta/pwa-192x192.png', 'sizes' => '192x192']]],
                ['name' => 'سجل الحضور', 'url' => PortalMode::appPath('/attendance'), 'icons' => [['src' => '/beta/pwa-192x192.png', 'sizes' => '192x192']]],
            ],
        ], 200, ['Content-Type' => 'application/manifest+json', 'Cache-Control' => 'no-cache'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
