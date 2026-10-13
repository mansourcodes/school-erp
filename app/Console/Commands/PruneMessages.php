<?php

namespace App\Console\Commands;

use App\Jobs\PruneOldMessages;
use Illuminate\Console\Command;

/** Messages are kept 90 days (PLAN.md §5.11, Q15); scheduled daily and also run from the admin dashboard. */
class PruneMessages extends Command
{
    protected $signature = 'messages:prune';

    protected $description = 'Soft-delete teacher messages older than the retention period';

    public function handle()
    {
        $count = PruneOldMessages::dispatchSync();
        $this->info("Pruned {$count} messages.");

        return 0;
    }
}
