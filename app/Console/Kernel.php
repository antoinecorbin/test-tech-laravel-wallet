<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel{

    public function schedule(Schedule $schedule)
    {
        $schedule->command('recurring-transfer:execution')
            ->dailyAt('02:00')
            ->withoutOverlapping();
    }
}
