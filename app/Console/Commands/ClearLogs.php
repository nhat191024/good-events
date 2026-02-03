<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClearLogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-logs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear application logs older than 7 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('ClearLogs command started.');

        $logPath = storage_path('logs');
        $files = glob($logPath . DIRECTORY_SEPARATOR . '*.log');
        $deletedCount = 0;
        foreach ($files as $file) {
            if (is_file($file) && filemtime($file) < now()->subDays(7)->timestamp) {
                unlink($file);
                $deletedCount++;
            }
        }
        $this->info("Deleted {$deletedCount} log files older than 7 days.");

        Log::info("ClearLogs command completed. Deleted {$deletedCount} log files.");
        return 0;
    }
}
