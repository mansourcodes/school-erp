<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\PruneOldMessages;
use Backpack\CRUD\app\Http\Controllers\AdminController as BackpackAdminController;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Backpack's dashboard, plus the daily message pruning: production has no scheduler cron, so
 * the first admin visit of the day runs it (config `backpack.base.setup_dashboard_routes` is
 * off and the routes are in routes/backpack/custom.php).
 */
class AdminController extends BackpackAdminController
{
    public function dashboard()
    {
        $this->pruneMessagesOncePerDay();

        return parent::dashboard();
    }

    protected function pruneMessagesOncePerDay()
    {
        // Cache::add only succeeds for the first caller of the day.
        if (! Cache::add('messages:pruned:'.today()->toDateString(), true, now()->endOfDay())) {
            return;
        }

        try {
            PruneOldMessages::dispatchSync();
        } catch (Throwable $e) {
            // The dashboard must open even if pruning fails; the next day tries again.
            report($e);
        }
    }
}
