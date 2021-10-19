<?php

namespace App\Console;

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
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            $connection = config('database.connections')[config('database.default')];
            $path = '~/db_backup/'.$connection['database'].'.sql.gz';
            // -x = lock all databases
            $cmdStr = 'mysqldump --user='.$connection['username']." --password='".$connection['password']."' -e -B ".$connection['database'].' | gzip > '.$path;
            try {
                exec($cmdStr);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        })->dailyAt('13:13');
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
