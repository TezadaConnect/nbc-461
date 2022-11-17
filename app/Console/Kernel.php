<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Models\BackupDatabase;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\ResearchCheck::class,
        Commands\DeadlineWeekly::class,
        Commands\DeadlineDaily::class,
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
        $schedule->command('research:weekly')->weekly();
        $schedule->command('deadline:weekly')->weekly();
        $schedule->command('deadline:daily')->daily();

        /**
         * Added this line for the Spatie DB Backup scheduling
         * The scheduler will run based on the frequency saved in
         * BackupDatabase table
         */
        $frequency= DB::table('backup_databases')->pluck('frequency');
        \Log::info($frequency);
        $schedule->command('backup:run --only-db')->daily()->when(function () {
            if ($frequency == 'daily'){
                return true;
            }
            else {
                return false;
             }
    
        });

        $schedule->command('backup:run --only-db')->weekly()->when(function () {
            if ($frequency == 'weekly'){
                return true;
            }
            else {
                return false;
             }
    
        });

        $schedule->command('backup:run --only-db')->monthly()->everyMinute(function () {
            if ($frequency == 'monthly'){
                return true;
            }
            else {
                return false;
             }
    
        });

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
