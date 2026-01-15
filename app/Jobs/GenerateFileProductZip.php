<?php

namespace App\Jobs;

use App\Models\FileProduct;
use App\Models\User;

use Filament\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use Throwable;

class GenerateFileProductZip implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * The number of seconds after which the job's unique lock will be released.
     */
    public int $uniqueFor = 3600;

    /**
     * Get the unique ID for the job.
     */
    public function uniqueId(): string
    {
        return 'generate-zip-' . $this->fileProduct->id . '-' . $this->hash;
    }

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FileProduct $fileProduct,
        public User $user,
        public string $hash,
        public int $billId
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->fileProduct->loadMissing('files');
        $files = $this->fileProduct->files;

        if ($files->isEmpty()) {
            return;
        }

        $zipFileName = sprintf('FPB-%s-designs.zip', $this->billId);
        $s3ZipPath = 'cached-zips/' . $this->fileProduct->id . '/' . $zipFileName;

        // Define temporary paths
        $tempId = Str::random(20);
        $sourceDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'zip-source-' . $tempId);
        $sourceZipPath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'zip-out-' . $tempId . '.zip');

        $zipStream = null;

        try {
            // Prepare source directory
            File::makeDirectory($sourceDir, 0755, true);

            foreach ($files as $file) {
                try {
                    $disk = $file->disk;
                    $path = str_replace('\\', '/', $file->path);

                    $readStream = Storage::disk($disk)->readStream($path);
                    if (!$readStream) {
                        continue;
                    }

                    $fileName = $file->file_name;
                    $destinationPath = $sourceDir . DIRECTORY_SEPARATOR . $fileName;

                    File::ensureDirectoryExists(dirname($destinationPath));

                    $writeStream = fopen($destinationPath, 'w');
                    stream_copy_to_stream($readStream, $writeStream);

                    if (is_resource($readStream)) {
                        fclose($readStream);
                    }
                    if (is_resource($writeStream)) {
                        fclose($writeStream);
                    }
                } catch (Throwable $ex) {
                    report($ex);
                }
            }

            $result = Process::path($sourceDir)->run([
                'zip',
                '-r',
                '-0',
                '-q',
                '-n',
                'psd:psb:tif',
                $sourceZipPath,
                '.'
            ]);

            if ($result->failed()) {
                throw new \Exception('System zip command failed: ' . $result->errorOutput());
            }

            if (!file_exists($sourceZipPath)) {
                throw new \Exception('Zip file was not generated.');
            }

            // Upload using stream
            $zipStream = fopen($sourceZipPath, 'r');
            Storage::disk('s3')->put(
                $s3ZipPath,
                $zipStream
            );
            if (is_resource($zipStream)) {
                fclose($zipStream);
            }

            $this->fileProduct->update([
                'cached_zip_path' => $s3ZipPath,
                'cached_zip_generated_at' => now(),
                'cached_zip_hash' => $this->hash,
            ]);

            $generatingKey = 'generating-zip:' . $this->fileProduct->id . ':' . $this->hash;
            Cache::forget($generatingKey);

            Notification::make()
                ->title('File ZIP của bạn đã sẵn sàng để tải xuống!')
                ->body('Bạn có thể tải xuống file ZIP từ trang đơn hàng của mình.')
                ->success()
                ->sendToDatabase([$this->user]);
        } catch (Throwable $ex) {
            $generatingKey = 'generating-zip:' . $this->fileProduct->id . ':' . $this->hash;
            Cache::forget($generatingKey);

            report($ex);
            throw $ex;
        } finally {
            if (isset($zipStream) && is_resource($zipStream)) {
                @fclose($zipStream);
            }

            if (File::isDirectory($sourceDir)) {
                File::deleteDirectory($sourceDir);
            }
            if (file_exists($sourceZipPath)) {
                @unlink($sourceZipPath);
            }
        }
    }
}
