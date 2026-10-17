<?php

namespace App\Support;

/**
 * Colour theme of the admin panel (Backpack), Backpack Settings key `admin.theme`. Set as
 * `data-admin-theme` on <html>; the colours live in public/css/admin-themes.css.
 */
class AdminTheme
{
    const DEFAULT = 'default';

    const ALL = [
        self::DEFAULT => 'الافتراضي',
        'ghadeer' => 'الغدير',
        'islamic-girly' => 'إسلامي للبنات',
        'brown' => 'بني',
        'wood' => 'خشبي',
        'gold' => 'ذهبي',
        'silver' => 'فضي',
        'dark-red' => 'أحمر داكن',
        'dark-blue' => 'أزرق داكن',
    ];

    public static function current()
    {
        $theme = config('settings.admin.theme');

        return is_string($theme) && array_key_exists($theme, self::ALL) ? $theme : self::DEFAULT;
    }
}
