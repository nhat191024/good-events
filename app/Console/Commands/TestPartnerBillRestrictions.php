<?php

namespace App\Console\Commands;

use App\Enum\PartnerBillStatus;
use App\Enum\Role;
use App\Models\Partner;
use App\Models\PartnerBill;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TestPartnerBillRestrictions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'partner-bill:test-restrictions
                            {action : lock, suspend, status, or reset}
                            {partner_id : Partner user ID}
                            {--bill= : Partner Bill ID required by the lock action}
                            {--force : Allow this command outside local and testing environments}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up or reset Partner Bill workflow-lock and account-suspension test states';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->environment(['local', 'testing']) && ! $this->option('force')) {
            $this->error('This command is restricted to local/testing. Use --force to run it elsewhere.');

            return self::FAILURE;
        }

        $action = (string) $this->argument('action');
        if (! in_array($action, ['lock', 'suspend', 'status', 'reset'], true)) {
            $this->error('Action must be one of: lock, suspend, status, reset.');

            return self::INVALID;
        }

        $partner = Partner::withTrashed()->find((int) $this->argument('partner_id'));
        if (! $partner || ! $partner->hasRole(Role::PARTNER)) {
            $this->error('Partner not found.');

            return self::FAILURE;
        }

        return match ($action) {
            'lock' => $this->lockWorkflow($partner),
            'suspend' => $this->suspendAccount($partner),
            'status' => $this->showStatus($partner),
            'reset' => $this->resetRestrictions($partner),
        };
    }

    private function lockWorkflow(Partner $partner): int
    {
        $billId = $this->option('bill');
        if (! $billId) {
            $this->error('The --bill option is required for the lock action.');

            return self::INVALID;
        }

        $bill = PartnerBill::query()
            ->whereKey((int) $billId)
            ->where('partner_id', $partner->id)
            ->first();

        if (! $bill) {
            $this->error('The bill does not belong to this Partner.');

            return self::FAILURE;
        }

        if (! in_array($bill->status, [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB], true)) {
            $this->error('The bill must have confirmed or in_job status.');

            return self::FAILURE;
        }

        $bill->forceFill(['completion_reminder_started_at' => now()])->saveQuietly();

        $this->info("Workflow locked using overdue bill {$bill->id} for Partner {$partner->id}.");
        $this->line('Use a different confirmed/in_job bill to verify PARTNER_WORKFLOW_LOCKED.');

        return self::SUCCESS;
    }

    private function suspendAccount(Partner $partner): int
    {
        if ($partner->trashed()) {
            $this->warn('The Partner account is already suspended or deleted.');

            return self::SUCCESS;
        }

        DB::transaction(function () use ($partner): void {
            $partner->forceFill([
                'ban_reason' => 'tạm khóa tài khoản do nghi ngờ tài khoản không hoạt động đúng quy trình hoặc đối tác ảo',
            ])->save();
            $partner->delete();
        });

        $this->info("Partner {$partner->id} suspended. Existing API tokens were preserved for contract testing.");

        return self::SUCCESS;
    }

    private function showStatus(Partner $partner): int
    {
        $overdueBillIds = PartnerBill::query()
            ->where('partner_id', $partner->id)
            ->whereIn('status', [PartnerBillStatus::CONFIRMED, PartnerBillStatus::IN_JOB])
            ->whereNotNull('completion_reminder_started_at')
            ->pluck('id')
            ->all();

        $this->table(['Partner', 'Suspended', 'Ban reason', 'Overdue bill IDs'], [[
            $partner->id,
            $partner->trashed() ? 'yes' : 'no',
            $partner->ban_reason ?? '-',
            $overdueBillIds ? implode(', ', $overdueBillIds) : '-',
        ]]);

        return self::SUCCESS;
    }

    private function resetRestrictions(Partner $partner): int
    {
        DB::transaction(function () use ($partner): void {
            if ($partner->trashed()) {
                $partner->restore();
            }

            $partner->forceFill(['ban_reason' => null])->save();

            PartnerBill::query()
                ->where('partner_id', $partner->id)
                ->update(['completion_reminder_started_at' => null]);
        });

        $this->info("Restrictions reset for Partner {$partner->id}.");

        return self::SUCCESS;
    }
}
