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
        // Delete mail logs older than the specified number of days
        $deletedRows = MailLog::where('created_at', '<', now()->subDays($days))->delete();

        $this->info("Deleted {$deletedRows} old mail logs.");
    }
}
