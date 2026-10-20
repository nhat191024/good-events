<?php

namespace App\Console\Commands;

use App\Enum\CacheKey;
use App\Enum\PartnerBillStatus;
use App\Models\Message;
use App\Models\PartnerBill;
use App\Models\Report;
use App\Models\Thread;
use Carbon\CarbonInterface;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class PruneOldChatThreads extends Command
{
    protected $signature = 'chat:prune-old
        {--days=14 : Delete threads inactive for this many days}
        {--chunk=100 : Number of threads to process per chunk}
        {--only-bills : Only prune chats linked to ended partner bills}
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
            ->chunkById($chunkSize, function ($threads) use (&$stats, $dryRun, $cutoff): void {
                foreach ($threads as $thread) {
                    try {
                        $counts = $this->pruneThread($thread->id, $cutoff, $dryRun);

                        if ($counts === null) {
                            $stats['skipped']++;

                            continue;
                        }

                        $stats['threads']++;
                        $stats['messages'] += $counts['messages'];
                        $stats['participants'] += $counts['participants'];
                        $stats['media'] += $counts['media'];

                        if (! $dryRun) {
                            Cache::forget(CacheKey::THREAD_PARTICIPANT->value.$thread->id);
                        }
                    } catch (Throwable $exception) {
                        report($exception);

                        $stats['failed']++;

                        $this->error("Failed to delete thread #{$thread->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->newLine();
        $this->info(($dryRun ? 'Would delete' : 'Deleted')." {$stats['threads']} thread(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted')." {$stats['messages']} message(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted')." {$stats['participants']} participant(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted')." {$stats['media']} media item(s).");

        if ($stats['skipped'] > 0) {
            $this->warn("Skipped {$stats['skipped']} thread(s) that are active, protected by reports, or linked to unfinished bills.");
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

    /** @return array{messages: int, participants: int, media: int}|null */
    private function pruneThread(int $threadId, CarbonInterface $cutoff, bool $dryRun): ?array
    {
        return DB::transaction(function () use ($threadId, $cutoff, $dryRun): ?array {
            $thread = Thread::withTrashed()->lockForUpdate()->find($threadId);

            if (! $thread || $thread->updated_at === null || $thread->updated_at->gte($cutoff)) {
                return null;
            }

            $bills = PartnerBill::query()->where('thread_id', $threadId)->lockForUpdate()->get();

            if (($this->option('only-bills') && $bills->isEmpty()) || $this->hasDeletionBlockers($thread, $bills)) {
                return null;
            }

            $counts = $this->countsForThread($thread);

            if ($dryRun) {
                return $counts;
            }

            foreach ($bills as $bill) {
                $bill->timestamps = false;

                try {
                    $bill->forceFill(['thread_id' => null])->saveQuietly();
                } finally {
                    $bill->timestamps = true;
                }
            }

            Message::withTrashed()
                ->where('thread_id', $threadId)
                ->with('media')
                ->chunkById(100, function ($messages): void {
                    foreach ($messages as $message) {
                        $message->forceDelete();
                    }
                });

            foreach (Participant::withTrashed()->where('thread_id', $threadId)->get() as $participant) {
                $participant->forceDelete();
            }

            $thread->forceDelete();

            return $counts;
        });
    }

    /** @param Collection<int, PartnerBill> $bills */
    private function hasDeletionBlockers(Thread $thread, Collection $bills): bool
    {
        if ($bills->contains(fn (PartnerBill $bill): bool => ! in_array($bill->status, [
            PartnerBillStatus::COMPLETED,
            PartnerBillStatus::CANCELLED,
            PartnerBillStatus::EXPIRED,
        ], true))) {
            return true;
        }

        return Report::query()
            ->where(function (Builder $query) use ($thread, $bills): void {
                $query->where('thread_id', $thread->id)
                    ->orWhereIn('reported_bill_id', $bills->modelKeys());
            })
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
