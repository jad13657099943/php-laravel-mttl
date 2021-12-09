<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Modules\Mttl\Http\Controllers\Api\JobController;
use Modules\Otc\Console\CancelExpireOtcTrade;
use Modules\TianYuan\Jobs\FundIssueStartJob;

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
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(function (JobController $controller){
                 $controller->job();
        })->everyMinute();

        $schedule->call(function (JobController $controller){
                 $controller->job2();
        })->everyMinute();

        $schedule->call(function (JobController $controller){
            $controller->job3();
        })->everyTwoMinutes();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
