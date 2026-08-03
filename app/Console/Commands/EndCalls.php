<?php

namespace App\Console\Commands;

use App\Events\CallUpdated;
use App\Http\Resources\Api\CallResource;
use App\Models\Call;
use App\Models\CallParticipant;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EndCalls extends Command
{
    /** @var string */
    protected $signature = 'calls:end
                            {call? : ULID của một cuộc gọi cụ thể}
                            {--thread= : Kết thúc mọi cuộc gọi chưa đóng trong thread này}
                            {--all : Kết thúc tất cả cuộc gọi chưa đóng}
                            {--force : Bắt buộc khi dùng --all}
                            {--no-broadcast : Không phát event CallUpdated}';

    /** @var string */
    protected $description = 'Kết thúc cuộc gọi bị treo hoặc được tạo trong quá trình kiểm thử';

    public function handle(): int
    {
        $callUuid = $this->argument('call');
        $threadId = $this->option('thread');
        $all = (bool) $this->option('all');
        $selectorCount = collect([$callUuid, $threadId, $all ? true : null])
            ->filter(fn ($value): bool => $value !== null && $value !== '')
            ->count();

        if ($selectorCount !== 1) {
            $this->error('Hãy truyền đúng một phạm vi: {call}, --thread=ID hoặc --all.');

            return self::INVALID;
        }

        if ($all && ! $this->option('force')) {
            $this->error('Tùy chọn --all yêu cầu thêm --force để tránh kết thúc nhầm toàn bộ cuộc gọi.');

            return self::INVALID;
        }

        if ($threadId !== null && filter_var($threadId, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
            $this->error('--thread phải là một số nguyên dương.');

            return self::INVALID;
        }

        $calls = $this->callsQuery($callUuid, $threadId, $all)->get();

        if ($calls->isEmpty()) {
            $this->warn('Không tìm thấy cuộc gọi chưa đóng phù hợp.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($calls): void {
            $callIds = $calls->pluck('id');

            Call::query()
                ->whereIn('id', $callIds)
                ->update([
                    'status' => Call::STATUS_ENDED,
                    'ended_at' => now(),
                    'updated_at' => now(),
                ]);

            CallParticipant::query()
                ->whereIn('call_id', $callIds)
                ->whereNull('left_at')
                ->update([
                    'left_at' => now(),
                    'updated_at' => now(),
                ]);
        });

        $endedCalls = Call::query()
            ->whereIn('id', $calls->pluck('id'))
            ->with([
                'initiator:id,name,avatar',
                'invites.user:id,name,avatar',
                'participants.user:id,name,avatar',
            ])
            ->get();

        if (! $this->option('no-broadcast')) {
            $this->broadcastEndedCalls($endedCalls);
        }

        $this->info("Đã kết thúc {$endedCalls->count()} cuộc gọi.");

        $this->table(
            ['Call ULID', 'Thread', 'Trạng thái'],
            $endedCalls->map(fn (Call $call): array => [
                $call->uuid,
                $call->thread_id,
                $call->status,
            ])->all(),
        );

        return self::SUCCESS;
    }

    private function callsQuery(mixed $callUuid, mixed $threadId, bool $all): Builder
    {
        return Call::query()
            ->whereIn('status', [Call::STATUS_RINGING, Call::STATUS_ACTIVE])
            ->when($callUuid !== null, fn (Builder $query) => $query->where('uuid', $callUuid))
            ->when($threadId !== null, fn (Builder $query) => $query->where('thread_id', (int) $threadId))
            ->when($all, fn (Builder $query) => $query->orderBy('id'));
    }

    /** @param Collection<int, Call> $calls */
    private function broadcastEndedCalls(Collection $calls): void
    {
        $request = Request::create('/');

        foreach ($calls as $call) {
            CallUpdated::dispatch(
                (new CallResource($call))->toArray($request)
            );
        }
    }
}
