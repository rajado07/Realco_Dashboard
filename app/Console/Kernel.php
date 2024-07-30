<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{

    protected $commands = [
        //
    ];

    protected function schedule(Schedule $schedule)
    {
        $schedule->command('generate:tasks')->everyMinute();
        // $schedule->command('update:task-status')->everyTenSeconds();
        // $schedule->command('run:tasks')->everyMinute();
        // $schedule->command('run:fastapi')->everyTwoMinutes();
        $schedule->command('run:fastapi')->everyMinute();
        
    }

    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
