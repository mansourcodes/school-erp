<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Logo of the admin panel, Backpack Setting `logo` (a path on the public disk, uploaded through
 * SettingCrudController). Shown on the admin login page and as the navbar brand.
 */
class AdminBranding
{
    public static function logoUrl()
    {
        $path = trim((string) config('settings.logo'));

        return $path === '' ? null : Storage::disk('public')->url($path);
    }
}
