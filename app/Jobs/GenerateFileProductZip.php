<?php

namespace App\Jobs;

use App\Models\FileProduct;
use App\Models\User;

use Filament\Notifications\Notification;

use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

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
        $tempZipPath = tempnam(sys_get_temp_dir(), 'zip_');

        try {
            if (!class_exists('\ZipStream\\ZipStream')) {
                throw new \Exception('ZipStream not installed');
            }

            // Create zip file
            $zipStreamClass = '\\ZipStream\\ZipStream';
            $outputStream = fopen($tempZipPath, 'w');

            $zip = new $zipStreamClass(
                outputStream: $outputStream,
                sendHttpHeaders: false,
                enableZip64: true
            );

            foreach ($files as $file) {
                try {
                    $disk = $file->disk;
                    $path = str_replace('\\', '/', $file->path);

                    $fileStream = Storage::disk($disk)->readStream($path);
                    if (!$fileStream) {
                        continue;
                    }

                    $fileName = $file->file_name;
                    $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

                    $storeExtensions = ['psd', 'psb', 'tif'];
                    $isStore = in_array($ext, $storeExtensions, true) || ($file->size && $file->size > 50 * 1024 * 1024);

                    $compressionMethod = $isStore ? \ZipStream\CompressionMethod::STORE : null;
                    $deflateLevel = $isStore ? 0 : null;

                    $zip->addFileFromStream($fileName, $fileStream, '', $compressionMethod, $deflateLevel);

                    if (is_resource($fileStream)) {
                        fclose($fileStream);
                    }
                } catch (Throwable $ex) {
                    report($ex);
                }
            }

            $zip->finish();
            fclose($outputStream);

            Storage::disk('s3')->put(
                $s3ZipPath,
                file_get_contents($tempZipPath)
            );

            $this->fileProduct->update([
                'cached_zip_path' => $s3ZipPath,
                'cached_zip_generated_at' => now(),
                'cached_zip_hash' => $this->hash,
            ]);

            @unlink($tempZipPath);
            $generatingKey = 'generating-zip:' . $this->fileProduct->id . ':' . $this->hash;
            Cache::forget($generatingKey);

            Notification::make()
                ->title('File ZIP của bạn đã sẵn sàng để tải xuống!')
                ->body('Bạn có thể tải xuống file ZIP từ trang đơn hàng của mình.')
                ->success()
                ->sendToDatabase([$this->user]);
        } catch (Throwable $ex) {
            @unlink($tempZipPath);

            $generatingKey = 'generating-zip:' . $this->fileProduct->id . ':' . $this->hash;
            Cache::forget($generatingKey);

            report($ex);
            throw $ex;
        }
    }
}
