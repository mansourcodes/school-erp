<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\PruneOldMessages;
use App\Services\Portal\PushOutbox;
use Backpack\CRUD\app\Http\Controllers\AdminController as BackpackAdminController;
use Illuminate\Support\Facades\Cache;
use Throwable;

/**
 * Backpack's dashboard, plus the daily housekeeping: production has no scheduler cron, so the first
 * admin visit of the day runs the message pruning and the push outbox tidy (config
 * `backpack.base.setup_dashboard_routes` is off and the routes are in routes/backpack/custom.php).
 */
class AdminController extends BackpackAdminController
{
    public function dashboard()
    {
        $this->onceToday('messages:pruned', function () {
            PruneOldMessages::dispatchSync();
        });
        // Status bookkeeping only: the outbox poll already ignores expired rows and retries stuck ones.
        $this->onceToday('push-outbox:tidied', function () {
            app(PushOutbox::class)->tidy();
        });

        return parent::dashboard();
    }

    protected function onceToday($key, callable $task)
    {
        // Cache::add only succeeds for the first caller of the day.
        if (! Cache::add($key . ':' . today()->toDateString(), true, now()->endOfDay())) {
            return;
        }

        try {
            $task();
        } catch (Throwable $e) {
            // The dashboard must open even if a task fails; the next day tries again.
            report($e);
        }
    }
}
