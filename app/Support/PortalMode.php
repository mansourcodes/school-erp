<?php

namespace App\Support;

/**
 * The portal switch (PLAN.md §4 "Portal mode switch", Q7): Backpack Settings key `portal.mode`.
 * beta (default): old portal is the default, new panel opt-in at /beta.
 * live: old portal URLs redirect to the new panel (only after the P0.2 platform upgrade, §11).
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
}
