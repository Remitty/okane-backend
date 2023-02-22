<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('6hourly:callbackcheck')->everySixHours()->runInBackground();
        $schedule->command('3hourly:checkaccount')->everyThreeHours()->runInBackground();
        // $schedule->command('hourly:checkOpenOrder')->weekdays()
        // ->hourly()
        // ->timezone('America/Chicago')
        // ->between('8:00', '17:00')
        // ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
