<?php

namespace App\Http\Controllers\Admin;

use App\Jobs\PruneOldMessages;
use App\Services\Portal\PushOutbox;
use App\Support\AdminDashboard;
use App\Support\QuranQuotes;
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

        $this->data['quote'] = QuranQuotes::random();
        $figures = backpack_user()->hasRole('Superadmin') ? AdminDashboard::cached() : [];
        foreach (['stats', 'attendance', 'classrooms', 'teachers', 'computed_at'] as $key) {
            $this->data[$key] = $figures[$key] ?? null;
        }

        return parent::dashboard();
    }

    /** The dashboard's refresh button: drop the cached figures so the next visit computes them again. */
    public function refreshDashboard()
    {
        abort_unless(backpack_user()->hasRole('Superadmin'), 403);

        AdminDashboard::forget();
        \Alert::success(__('base.dashboard.refreshed'))->flash();

        return redirect()->route('backpack.dashboard');
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
