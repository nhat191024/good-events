<?php

namespace App\Console\Commands;

use App\Enum\ChatMembershipContext;
use App\Models\ChatInvitation;
use Cmgmyr\Messenger\Models\Models;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BackfillChatParticipantMembershipContext extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'chat:backfill-participant-context
                            {--chunk=500 : Number of participants to process per chunk}
                            {--dry-run : Report changes without updating participants}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill the membership context of existing chat participants';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');

        if ($chunkSize < 1) {
            $this->error('The --chunk option must be at least 1.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $participantTable = Models::table('participants');
        $updated = 0;

        DB::table($participantTable)
            ->select(['id', 'thread_id', 'user_id', 'membership_context'])
            ->orderBy('id')
            ->chunkById($chunkSize, function (Collection $participants) use ($participantTable, $dryRun, &$updated): void {
                $contextsByParticipantId = $this->resolveContexts($participants);

                foreach ($contextsByParticipantId->groupBy(fn (string $context): string => $context) as $context => $participantIds) {
                    $ids = $participantIds->keys()->all();
                    $updated += count($ids);

                    if (! $dryRun) {
                        DB::table($participantTable)
                            ->whereIn('id', $ids)
                            ->update(['membership_context' => $context]);
                    }
                }
            });

        $action = $dryRun ? 'Would update' : 'Updated';
        $this->info("{$action} {$updated} chat participant(s).");

        return self::SUCCESS;
    }

    /**
     * @param  Collection<int, object{id: int, thread_id: int, user_id: int, membership_context: string|null}>  $participants
     * @return Collection<int, string>
     */
    private function resolveContexts(Collection $participants): Collection
    {
        $threadIds = $participants->pluck('thread_id')->unique()->values();
        $userIds = $participants->pluck('user_id')->unique()->values();

        $invitationKeys = DB::table('chat_invitations')
            ->whereIn('thread_id', $threadIds)
            ->whereIn('user_id', $userIds)
            ->whereIn('status', [ChatInvitation::STATUS_ACCEPTED, ChatInvitation::STATUS_LEFT])
            ->get(['thread_id', 'user_id'])
            ->mapWithKeys(fn (object $invitation): array => [
                $this->membershipKey($invitation->thread_id, $invitation->user_id) => true,
            ]);

        $billsByThreadId = DB::table('partner_bills')
            ->whereIn('thread_id', $threadIds)
            ->get(['thread_id', 'client_id', 'partner_id'])
            ->keyBy('thread_id');

        return $participants
            ->mapWithKeys(function (object $participant) use ($billsByThreadId, $invitationKeys): array {
                $bill = $billsByThreadId->get($participant->thread_id);
                $context = match (true) {
                    $invitationKeys->has($this->membershipKey($participant->thread_id, $participant->user_id)) => ChatMembershipContext::Invitation,
                    $bill !== null && (int) $bill->client_id === (int) $participant->user_id => ChatMembershipContext::Client,
                    $bill !== null && (int) $bill->partner_id === (int) $participant->user_id => ChatMembershipContext::Partner,
                    default => ChatMembershipContext::System,
                };

                if ($participant->membership_context === $context->value) {
                    return [];
                }

                return [$participant->id => $context->value];
            });
    }

    private function membershipKey(int $threadId, int $userId): string
    {
        return "{$threadId}:{$userId}";
    }
}
