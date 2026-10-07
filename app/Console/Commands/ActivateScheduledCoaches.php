<?php

namespace App\Console\Commands;

use App\Services\CoachConfirmationService;
use Illuminate\Console\Command;

class ActivateScheduledCoaches extends Command
{
    protected $signature = 'coaches:activate-scheduled';

    protected $description = 'Assign coaches whose approved renewal has reached its start date';

    public function handle(CoachConfirmationService $service): int
    {
        $count = $service->activateDue();
        $this->info("Activated {$count} scheduled coach assignment(s).");

        return self::SUCCESS;
    }
}