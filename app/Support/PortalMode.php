<?php

namespace App\Support;

/**
 * The portal switch (PLAN.md §4 "Portal mode switch", Q7): Backpack Settings key `portal.mode`.
 * beta (default): old portal is the default, new panel opt-in at /beta.
 * live: the new panel moves to the root (/, /login, deep links); /beta and the old portal URLs redirect there.
 * maintenance / closed: both portals show a notice; /admin is never affected.
 */
class PortalMode
{
    const BETA = 'beta';
    const LIVE = 'live';
    const MAINTENANCE = 'maintenance';
    const CLOSED = 'closed';

    const ALL = [self::BETA, self::LIVE, self::MAINTENANCE, self::CLOSED];

    public static function current()
    {
        $mode = config('settings.portal.mode');

        return in_array($mode, self::ALL, true) ? $mode : self::BETA;
    }

    public static function is($mode)
    {
        return self::current() === $mode;
    }

    /**
     * URL of a new-panel page: at the root once live (PLAN.md §4 "Promotion"), under /beta otherwise.
     * The build stays in public/beta, so asset URLs keep /beta/ in every mode.
     */
    public static function appPath($path = '/')
    {
        $path = '/' . ltrim($path, '/');

        return self::is(self::LIVE) ? $path : '/beta' . $path;
    }
}
