<?php

namespace App\Console\Commands;

use App\Models\Message;
use App\Models\PartnerBill;
use App\Models\Thread;
use Carbon\CarbonInterface;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

class PruneOldPartnerBillChats extends Command
{
    protected $signature = 'partner-bills:prune-old-chat-media
        {--days=14 : Delete bill media and chats for bills not updated for this many days}
        {--chunk=100 : Number of bills to process per chunk}
        {--dry-run : Show what would be deleted without deleting anything}';

    protected $description = 'Delete media and chat data for old partner bills while keeping the partner bill records.';

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
            'bills' => 0,
            'bill_media' => 0,
            'threads' => 0,
            'messages' => 0,
            'participants' => 0,
            'chat_media' => 0,
            'failed' => 0,
        ];

        $this->info(sprintf(
            '%s media and chats for partner bills older than %s.',
            $dryRun ? 'Scanning' : 'Deleting',
            $cutoff->toDateTimeString(),
        ));

        $this->oldPartnerBillsQuery($cutoff)
            ->chunkById($chunkSize, function ($partnerBills) use (&$stats, $dryRun): void {
                foreach ($partnerBills as $partnerBill) {
                    $counts = $this->countsForPartnerBill($partnerBill);

                    if ($dryRun) {
                        $this->addCountsToStats($stats, $counts);

                        continue;
                    }

                    try {
                        DB::transaction(function () use ($partnerBill, $counts, &$stats): void {
                            $this->deletePartnerBillMedia($partnerBill);
                            $this->deletePartnerBillChat($partnerBill);
                            $this->addCountsToStats($stats, $counts);
                        });
                    } catch (Throwable $exception) {
                        report($exception);

                        $stats['failed']++;

                        $this->error("Failed to prune partner bill #{$partnerBill->id}: {$exception->getMessage()}");
                    }
                }
            });

        $this->newLine();
        $this->info(($dryRun ? 'Would process' : 'Processed') . " {$stats['bills']} partner bill(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['bill_media']} partner bill media item(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['threads']} chat thread(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['messages']} chat message(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['participants']} chat participant(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['chat_media']} chat media item(s).");

        if ($stats['failed'] > 0) {
            $this->error("Failed to prune {$stats['failed']} partner bill(s).");

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function oldPartnerBillsQuery(CarbonInterface $cutoff): Builder
    {
        return PartnerBill::query()
            ->where('updated_at', '<', $cutoff)
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('thread_id')
                    ->orWhereHas('media');
            })
            ->orderBy('id');
    }

    /**
     * @return array{bills: int, bill_media: int, threads: int, messages: int, participants: int, chat_media: int}
     */
    private function countsForPartnerBill(PartnerBill $partnerBill): array
    {
        $messageIds = Message::withTrashed()
            ->where('thread_id', $partnerBill->thread_id)
            ->select('id');

        return [
            'bills' => 1,
            'bill_media' => Media::query()
                ->where('model_type', PartnerBill::class)
                ->where('model_id', $partnerBill->id)
                ->count(),
            'threads' => (int) (
                $partnerBill->thread_id
                && Thread::withTrashed()->whereKey($partnerBill->thread_id)->exists()
            ),
            'messages' => $partnerBill->thread_id
                ? Message::withTrashed()
                    ->where('thread_id', $partnerBill->thread_id)
                    ->count()
                : 0,
            'participants' => $partnerBill->thread_id
                ? Participant::withTrashed()
                    ->where('thread_id', $partnerBill->thread_id)
                    ->count()
                : 0,
            'chat_media' => $partnerBill->thread_id
                ? Media::query()
                    ->where('model_type', Message::class)
                    ->whereIn('model_id', $messageIds)
                    ->count()
                : 0,
        ];
    }

    private function deletePartnerBillMedia(PartnerBill $partnerBill): void
    {
        $partnerBill
            ->media()
            ->get()
            ->each(fn (Media $media) => $media->delete());
    }

    private function deletePartnerBillChat(PartnerBill $partnerBill): void
    {
        if (! $partnerBill->thread_id) {
            return;
        }

        $thread = Thread::withTrashed()->find($partnerBill->thread_id);

        $this->detachThreadFromPartnerBill($partnerBill);

        if (! $thread) {
            return;
        }

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
    }

    private function detachThreadFromPartnerBill(PartnerBill $partnerBill): void
    {
        $partnerBill->timestamps = false;

        try {
            $partnerBill
                ->forceFill(['thread_id' => null])
                ->saveQuietly();
        } finally {
            $partnerBill->timestamps = true;
        }
    }

    /**
     * @param array{bills: int, bill_media: int, threads: int, messages: int, participants: int, chat_media: int, failed: int} $stats
     * @param array{bills: int, bill_media: int, threads: int, messages: int, participants: int, chat_media: int} $counts
     */
    private function addCountsToStats(array &$stats, array $counts): void
    {
        $stats['bills'] += $counts['bills'];
        $stats['bill_media'] += $counts['bill_media'];
        $stats['threads'] += $counts['threads'];
        $stats['messages'] += $counts['messages'];
        $stats['participants'] += $counts['participants'];
        $stats['chat_media'] += $counts['chat_media'];
    }
}
