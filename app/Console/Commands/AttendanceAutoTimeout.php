<?php

namespace App\Console\Commands;

use App\Models\Attendance;
use Illuminate\Console\Command;

class AttendanceAutoTimeout extends Command
{
    protected $signature   = 'attendance:auto-timeout';
    protected $description = 'Auto time-out anyone still inside after 12 hours';

    public function handle(): int
    {
        $n = Attendance::closeOverdue();
        $this->info("Auto timed-out {$n} record(s).");

        return self::SUCCESS;
    }
}