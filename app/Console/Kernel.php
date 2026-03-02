<?php

namespace App\Console;

use App\Models\RentalHist;
use App\Services\ReturnDeadlineNotificationService;
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
        //端末返却日をチェック
        $schedule->call(function () {
            $check_days = [0, 3]; //0:当日, 3:3日後
            $rental = new RentalHist();
            $notificationService = new ReturnDeadlineNotificationService();
            foreach ($check_days as $day) {
                $message = $rental->deadLineCheck($day);
                if ($message) {
                    $notificationService->send($message);
                }
            }
        })->dailyAt('09:30');

        // 古いバックアップファイルを削除
        $schedule->command('backup:clean --disable-notifications')->weekly();
        // DBのバックアップ
        $schedule->command('backup:run --disable-notifications --only-db')->weekly();
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
