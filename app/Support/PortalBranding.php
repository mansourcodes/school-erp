<?php

namespace App\Support;

/**
 * Texts of the new panel (/beta), Backpack Settings `portal.name`, `portal.short_name`, `portal.centre`,
 * `portal.description`. Images (logo, icons) stay files in frontend/public.
 */
class PortalBranding
{
    const KEYS = ['name', 'short_name', 'centre', 'description'];

    /** @return array{name: string, short_name: string, centre: string, description: string} */
    public static function all()
    {
        $values = [];
        foreach (self::KEYS as $key) {
            $values[$key] = self::get($key);
        }

        return $values;
    }

    public static function get($key)
    {
        $value = trim((string) config('settings.portal.' . $key));

        // An emptied name falls back to the full name (or the centre), never to a blank title.
        if ($value === '' && $key !== 'name') {
            return $key === 'centre' ? self::get('name') : (self::get('centre') ?: self::get('name'));
        }

        return $value;
    }
}
