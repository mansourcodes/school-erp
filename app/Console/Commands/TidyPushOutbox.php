<?php

namespace App\Console\Commands;

use App\Services\Portal\PushOutbox;
use Illuminate\Console\Command;

/** Push outbox bookkeeping (PLAN.md §5.12), out of the teachers' polls; scheduled hourly. */
class TidyPushOutbox extends Command
{
    protected $signature = 'push:outbox-tidy';

    protected $description = 'Mark expired push deliveries skipped and stuck sends failed';

    public function handle(PushOutbox $outbox)
    {
        $count = $outbox->tidy();
        $this->info("Updated {$count} push deliveries.");

        return 0;
    }
}
