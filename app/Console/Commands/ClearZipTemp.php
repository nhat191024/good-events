<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use Illuminate\Support\Facades\Log;

class ClearZipTemp extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clear-zip-temp';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear temporary generated zip files';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Log::info('ClearZipTemp command started.');
        $dir = storage_path('app/temp');
        if (!is_dir($dir)) {
            $this->info("Directory does not exist: {$dir}");
            return 0;
        }

        $sourceFiles = glob($dir . DIRECTORY_SEPARATOR . 'zip-source-*');
        $deletedCount = 0;
        foreach ($sourceFiles as $filePath) {
            if (is_dir($filePath)) {
                $this->deleteDirectory($filePath);
                $deletedCount++;
            }
        }
        Log::info("ClearZipTemp command completed. Deleted {$deletedCount} zip source directories.");
        $this->info("Deleted {$deletedCount} temporary zip source directories.");

        $outFiles = glob($dir . DIRECTORY_SEPARATOR . 'zip-out-*');
        $deletedCount = 0;
        foreach ($outFiles as $filePath) {
            if (is_file($filePath)) {
                unlink($filePath);
                $deletedCount++;
            }
        }
        Log::info("ClearZipTemp command completed. Deleted {$deletedCount} zip output files.");
        $this->info("Deleted {$deletedCount} temporary zip output files.");

        return 0;
    }

    protected function deleteDirectory($dirPath)
    {
        if (!is_dir($dirPath)) {
            return;
        }
        $files = scandir($dirPath);
        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }
            $fullPath = $dirPath . DIRECTORY_SEPARATOR . $file;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        rmdir($dirPath);
    }
}
