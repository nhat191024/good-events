<?php

namespace App\Console\Commands;

use Spatie\Activitylog\Models\Activity;

use Illuminate\Console\Command;

class ClearActivityLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-activityLog {--days=30 : The number of days to retain activity logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old activity logs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = (int) $this->option('days');

        if ($days <= 0) {
            $this->error('The number of days must be a positive integer.');
            return 1;
        }

        $cutoffDate = now()->subDays($days);

        $deletedRows = Activity::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deletedRows} rows from activity logs. Days retained: {$days}.");

        return 0;
    }
}
