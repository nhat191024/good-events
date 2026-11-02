<?php

namespace App\Console\Commands;

use App\Enum\PartnerBillStatus;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class CancelBillsForTrashedClients extends Command
{
    protected $signature = 'partner-bills:cancel-for-trashed-clients
                            {--chunk=500 : Number of clients and bills to process per chunk}
                            {--dry-run : Report matching bills without cancelling them}';

    protected $description = 'Cancel all partner bills belonging to soft-deleted clients';

    public function handle(): int
    {
        $chunkSize = filter_var($this->option('chunk'), FILTER_VALIDATE_INT, [
            'options' => ['min_range' => 1],
        ]);

        if ($chunkSize === false) {
            $this->error('The --chunk option must be a positive integer.');

            return self::FAILURE;
        }

        $dryRun = (bool) $this->option('dry-run');
        $processedClients = 0;
        $cancelledBills = 0;

        User::onlyTrashed()
            ->select('id', 'deleted_at')
            ->whereHas('partnerBillsAsClient', fn (Builder $query): Builder => $query->where('status', '!=', PartnerBillStatus::CANCELLED))
            ->chunkById($chunkSize, function (Collection $clients) use ($chunkSize, $dryRun, &$processedClients, &$cancelledBills): void {
                foreach ($clients as $client) {
                    $cancelledBills += $dryRun
                        ? $client->partnerBillsAsClient()->where('status', '!=', PartnerBillStatus::CANCELLED)->count()
                        : $client->cancelPartnerBillsAsClient($chunkSize);

                    $processedClients++;
                }
            });

        $action = $dryRun ? 'Would cancel' : 'Cancelled';
        $this->info("{$action} {$cancelledBills} partner bill(s) across {$processedClients} soft-deleted client(s).");

        return self::SUCCESS;
    }
}
