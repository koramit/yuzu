<?php

namespace App\Console;

use App\Models\DutyToken;
use Exception;
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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // create duty token every sunday 20:14 UTC => monday pie time
        $schedule->call(fn () => DutyToken::generate())->weeklyOn(7, '20:14');

        // drink water

        // clear patients
        // ->dailyAt('08:40'); => 15:40
        // ->dailyAt('09:15'); => 16:15
        // ->dailyAt('09:30'); => 16:30
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
