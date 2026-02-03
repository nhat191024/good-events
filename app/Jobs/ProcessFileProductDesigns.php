<?php

namespace App\Jobs;

use App\Models\FileProduct;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Arr;

class ProcessFileProductDesigns implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public FileProduct $record,
        public array $designPaths
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $designPaths = Arr::wrap($this->designPaths);

        // Get current media to manage deletions and updates
        $existingMedia = $this->record->media()
            ->where('collection_name', 'designs')
            ->get();

        $keptMediaIds = [];

        foreach ($designPaths as $index => $path) {
            // Find if this path belongs to an existing media
            $mediaItem = $existingMedia->first(function ($media) use ($path) {
                return $media->getPath() === $path;
            });

            if ($mediaItem) {
                $mediaItem->order_column = $index + 1;
                $mediaItem->save();
                $keptMediaIds[] = $mediaItem->id;
            } else {
                // New file
                $media = $this->record->addMediaFromDisk($path, 's3')
                    ->toMediaCollection('designs');

                $media->order_column = $index + 1;
                $media->save();
                $keptMediaIds[] = $media->id;
            }
        }

        // Delete removed media
        $existingMedia->reject(fn ($media) => in_array($media->id, $keptMediaIds))
            ->each->delete();
    }
}
