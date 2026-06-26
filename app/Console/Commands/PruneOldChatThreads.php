<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\PartnerBill;
use App\Models\Report;
use App\Models\Thread;
use Carbon\CarbonInterface;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class PruneOldChatThreads extends Command
{
    protected $signature = 'chat:prune-old
        {--days=14 : Delete threads inactive for this many days}
        {--chunk=100 : Number of threads to process per chunk}
        {--dry-run : Show what would be deleted without deleting anything}';

    protected $description = 'Permanently delete old chat threads, messages, participants, and message media.';

    public function handle(): int
    {
        $days = (int) $this->option('days');
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        if ($days <= 0) {
            $this->error('The --days option must be a positive integer.');

            return self::FAILURE;
        }

        if ($chunkSize <= 0) {
            $this->error('The --chunk option must be a positive integer.');

            return self::FAILURE;
        }

        $cutoff = now()->subDays($days);

        $stats = [
            'threads' => 0,
            'messages' => 0,
            'participants' => 0,
            'media' => 0,
            'skipped' => 0,
            'failed' => 0,
        ];

        $this->info(sprintf(
            '%s chat threads older than %s.',
            $dryRun ? 'Scanning' : 'Deleting',
            $cutoff->toDateTimeString(),
        ));

        $this->oldThreadsQuery($cutoff)
            ->chunkById($chunkSize, function ($threads) use (&$stats, $dryRun): void {
                foreach ($threads as $thread) {
                    if ($this->hasDeletionBlockers($thread)) {
                        $stats['skipped']++;

                        $this->warn("Skipped thread #{$thread->id} because it is linked to a bill or report.");

                        continue;
                    }

                    $counts = $this->countsForThread($thread);

                    if ($dryRun) {
                        $stats['threads']++;
                        $stats['messages'] += $counts['messages'];
                        $stats['participants'] += $counts['participants'];
                        $stats['media'] += $counts['media'];

                        continue;
                    }

                    try {
                        DB::transaction(function () use ($thread, $counts, &$stats): void {
                            Message::withTrashed()
                                ->where('thread_id', $thread->id)
                                ->with('media')
                                ->chunkById(100, function ($messages): void {
                                    foreach ($messages as $message) {
                                        $message->clearMediaCollection(Message::MEDIA_COLLECTION_CHAT_IMAGES);
                                        $message->forceDelete();
                                    }
                                });

                            $participants = Participant::withTrashed()
                                ->where('thread_id', $thread->id)
                                ->get();

                            foreach ($participants as $participant) {
                                $participant->forceDelete();
                            }

                            $thread->forceDelete();

                            $stats['threads']++;
                            $stats['messages'] += $counts['messages'];
                            $stats['participants'] += $counts['participants'];
                            $stats['media'] += $counts['media'];
                        });
                    } catch (Throwable $exception) {
                        report($exception);

                        $stats['failed']++;

                        $this->error("Failed to delete thread #{$thread->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->newLine();
        $this->info(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['threads']} thread(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['messages']} message(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['participants']} participant(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['media']} media item(s).");

        if ($stats['skipped'] > 0) {
            $this->warn("Skipped {$stats['skipped']} thread(s) linked to a bill or report.");
        }

        if ($stats['failed'] > 0) {
            $this->error("Failed to delete {$stats['failed']} thread(s).");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function oldThreadsQuery(CarbonInterface $cutoff): Builder
    {
        return Thread::withTrashed()
            ->where('updated_at', '<', $cutoff)
            ->orderBy('id');
    }

    private function hasDeletionBlockers(Thread $thread): bool
    {
        return PartnerBill::query()
            ->where('thread_id', $thread->id)
            ->exists()
            || Report::query()
                ->where('thread_id', $thread->id)
                ->exists();
    }

    /**
     * @return array{messages: int, participants: int, media: int}
     */
    private function countsForThread(Thread $thread): array
    {
        return [
            'messages' => Message::withTrashed()
                ->where('thread_id', $thread->id)
                ->count(),
            'participants' => Participant::withTrashed()
                ->where('thread_id', $thread->id)
                ->count(),
            'media' => Media::query()
                ->where('model_type', Message::class)
                ->whereIn(
                    'model_id',
                    Message::withTrashed()
                        ->where('thread_id', $thread->id)
                        ->select('id'),
                )
                ->count(),
        ];
    }
}
