<?php

namespace App\Support;

/**
 * Colour theme of the new panel (/beta), one for the whole centre: Backpack Settings key `portal.theme`.
 * Each value is a DaisyUI theme name defined in frontend/src/app/styles/app.css.
 */
class PortalTheme
{
    const GHADEER = 'ghadeer';
    const GIRLS = 'girls';
    const CUPCAKE = 'cupcake';
    const AUTUMN = 'autumn';
    const WIREFRAME = 'wireframe';

    const ALL = [self::GHADEER, self::GIRLS, self::CUPCAKE, self::AUTUMN, self::WIREFRAME];

    public static function current()
    {
        $theme = config('settings.portal.theme');

        return in_array($theme, self::ALL, true) ? $theme : self::GHADEER;
    }
}
