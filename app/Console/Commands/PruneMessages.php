<?php

namespace App\Console\Commands;

use App\Models\Message;
use Illuminate\Console\Command;

/** Messages are kept 90 days (PLAN.md §5.11, Q15); scheduled daily. */
class PruneMessages extends Command
{
    protected $signature = 'messages:prune';

    protected $description = 'Soft-delete teacher messages older than the retention period';

    public function handle()
    {
        $count = Message::where('created_at', '<', now()->subDays(Message::RETENTION_DAYS))->delete();
        $this->info("Pruned {$count} messages.");

        return 0;
    }
}
