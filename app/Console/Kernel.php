<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [

    ];

    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('advertisement:update-status')->dailyAt('01:00');
        // $schedule->call('App\Http\Controllers\API\CommissionHistoryController@store');
        $schedule->call('App\Http\Controllers\BookingController@reminder')->daily();
        $schedule->command('booking:cancel-expired')->dailyAt('01:00');
        $schedule->command('security:audits')->dailyAt('02:00');
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
