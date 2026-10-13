<?php

namespace App\Jobs;

use App\Models\Message;
use Illuminate\Foundation\Bus\Dispatchable;

/**
 * Soft-deletes teacher messages older than the retention period (PLAN.md §5.11, Q15).
 * Run with dispatchSync(): from `messages:prune` and once a day from the admin dashboard,
 * because production has no scheduler cron (_context/feature/03-platform-upgrade.md, Part C).
 */
class PruneOldMessages
{
    use Dispatchable;

    /** @return int number of messages pruned */
    public function handle()
    {
        return Message::where('created_at', '<', now()->subDays(Message::RETENTION_DAYS))->delete();
    }
}
