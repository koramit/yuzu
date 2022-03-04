<?php

namespace App\Console;

use App\Models\DutyToken;
use App\Tasks\ClearPatientNotification;
use App\Tasks\CroissantNeedHelpNotification;
use App\Tasks\DrinkWaterNotification;
use App\Tasks\FetchLabCovid;
use App\Tasks\UpdatePatientName;
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
        $schedule->call(fn () => DrinkWaterNotification::run())->dailyAt('23:06'); // 06:06
        $schedule->call(fn () => DrinkWaterNotification::run())->dailyAt('02:09'); // 09:09
        $schedule->call(fn () => DrinkWaterNotification::run())->dailyAt('04:11'); // 11:11
        $schedule->call(fn () => DrinkWaterNotification::run())->dailyAt('07:14'); // 14:14
        $schedule->call(fn () => DrinkWaterNotification::run())->dailyAt('10:17'); // 17:17
        $schedule->call(fn () => DrinkWaterNotification::run())->dailyAt('13:20'); // 20:20

        // clear patients
        $schedule->call(fn () => ClearPatientNotification::run())->dailyAt('08:40'); // 15:40
        $schedule->call(fn () => ClearPatientNotification::run())->dailyAt('09:15'); // 16:15
        $schedule->call(fn () => ClearPatientNotification::run())->dailyAt('09:30'); // 16:30

        // croissant need help
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('11:20'); // 18:20
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('11:50'); // 18:50
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('12:20'); // 19:20
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('12:50'); // 19:50
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('13:20'); // 20:20
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('13:50'); // 20:50
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('14:20'); // 21:20
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('14:50'); // 21:50
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('15:20'); // 22:20
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('15:50'); // 22:50
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('16:20'); // 23:20
        $schedule->call(fn () => CroissantNeedHelpNotification::run())->dailyAt('16:50'); // 23:50

        // update patient name after clinic closed for certification
        $schedule->call(fn () => UpdatePatientName::run())->dailyAt('12:00'); // 19:00

        // fetch LIS API
        $schedule->call(fn () => FetchLabCovid::run())->everyTenMinutes(); // hours define in task
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
