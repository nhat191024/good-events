<?php

namespace App\Console\Commands;

use Tapp\FilamentMailLog\Models\MailLog;

use Illuminate\Console\Command;

class ClearMailLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-mailLog {--days=7 : The number of days to retain mail logs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear old mail logs from the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $days = $this->option('days');

        if ($days <= 0) {
            $this->error('The number of days must be a positive integer.');
            return;
        }

        $cutoffDate = now()->subDays($days);

        $deletedRows = MailLog::where('created_at', '<', $cutoffDate)->delete();

        $this->info("Deleted {$deletedRows} old mail logs. Days retained: {$days}.");
    }
}
