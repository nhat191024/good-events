<?php

namespace App\Jobs;

use App\Models\FileProduct;
use App\Models\User;

use Aws\Exception\MultipartUploadException;
use Aws\S3\MultipartUploader;

use Filament\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
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
     * The number of seconds the job can run before timing out.
     */
    public int $timeout = 3600;

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
        Log::info("GenerateFileProductZip: Starting job for Product: {$this->fileProduct->id}, Hash: {$this->hash}");

        $this->fileProduct->loadMissing('files');
        $files = $this->fileProduct->files;

        if ($files->isEmpty()) {
            Log::warning("GenerateFileProductZip: No files found for product {$this->fileProduct->id}");
            return;
        }

        Log::info("GenerateFileProductZip: Found {$files->count()} files.");

        $zipFileName = sprintf('FPB-%s-designs.zip', $this->fileProduct->id);
        $s3ZipPath = 'cached-zips/' . $this->fileProduct->id . '/' . $zipFileName;

        // Define temporary paths
        $tempId = Str::random(20);
        $sourceDir = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'zip-source-' . $tempId);
        $sourceZipPath = storage_path('app' . DIRECTORY_SEPARATOR . 'temp' . DIRECTORY_SEPARATOR . 'zip-out-' . $tempId . '.zip');

        Log::info("GenerateFileProductZip: Source Dir: $sourceDir, Zip Path: $sourceZipPath");

        $zipStream = null;

        try {
            // Prepare source directory
            File::makeDirectory($sourceDir, 0755, true);

            foreach ($files as $file) {
                try {
                    $disk = $file->disk;
                    $path = str_replace('\\', '/', $file->path);

                    Log::info("GenerateFileProductZip: Processing file {$file->file_name} from disk {$disk} path {$path}");

                    $readStream = Storage::disk($disk)->readStream($path);
                    if (!$readStream) {
                        Log::warning("GenerateFileProductZip: Could not read stream for file {$path}");
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
                    Log::error("GenerateFileProductZip: Error processing file {$file->file_name}: " . $ex->getMessage());
                    report($ex);
                }
            }

            Log::info("GenerateFileProductZip: Files prepared. Starting zip process.");

            // Removed -n and psd:psb:tif as we are using -0 (store only) which implies no compression
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

            Log::info("GenerateFileProductZip: Zip command executed.");

            if ($result->failed()) {
                Log::error("GenerateFileProductZip: System zip command failed: " . $result->errorOutput());
                throw new \Exception('System zip command failed: ' . $result->errorOutput());
            }

            if (!file_exists($sourceZipPath)) {
                Log::error("GenerateFileProductZip: Zip file was not generated at $sourceZipPath");
                throw new \Exception('Zip file was not generated.');
            }

            Log::info("GenerateFileProductZip: Zip generated successfully. Size: " . filesize($sourceZipPath));

            // Upload using MultipartUploader
            Log::info("GenerateFileProductZip: Uploading zip to S3 (Multipart): $s3ZipPath");

            $disk = Storage::disk('s3');
            $client = $disk->getClient();
            $bucket = config('filesystems.disks.s3.bucket');

            $uploader = new MultipartUploader($client, $sourceZipPath, [
                'bucket' => $bucket,
                'key'    => $s3ZipPath,
            ]);

            try {
                $result = $uploader->upload();
                Log::info("GenerateFileProductZip: Upload completed. ObjectURL: " . $result['ObjectURL']);
            } catch (MultipartUploadException $e) {
                Log::error("GenerateFileProductZip: Multipart upload failed: " . $e->getMessage());
                throw $e;
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
            Log::error("GenerateFileProductZip: Job failed with exception: " . $ex->getMessage());
            Log::error($ex->getTraceAsString());

            $generatingKey = 'generating-zip:' . $this->fileProduct->id . ':' . $this->hash;
            Cache::forget($generatingKey);

            report($ex);
            throw $ex;
        } finally {
            Log::info("GenerateFileProductZip: Cleaning up temp files.");
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
