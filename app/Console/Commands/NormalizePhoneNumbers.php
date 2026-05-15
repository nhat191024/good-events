<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\PhoneLoginService;
use Illuminate\Console\Command;

class NormalizePhoneNumbers extends Command
{
    protected $signature = 'users:normalize-phones
                            {--dry-run : Preview changes without saving to the database}';

    protected $description = 'Normalize all user phone numbers in the database to the 0XXXXXXXXX format';

    public function handle(PhoneLoginService $phoneService): void
    {
        $dryRun = $this->option('dry-run');

        $users = User::whereNotNull('phone')->get(['id', 'phone']);

        if ($users->isEmpty()) {
            $this->info('No users with phone numbers found.');

            return;
        }

        $changed = 0;

        foreach ($users as $user) {
            $normalized = $phoneService->normalizePhone($user->phone);

            if ($normalized === $user->phone) {
                continue;
            }

            $this->line("  User #{$user->id}: {$user->phone} → {$normalized}");

            if (! $dryRun) {
                $user->phone = $normalized;
                $user->save();
            }

            $changed++;
        }

        if ($changed === 0) {
            $this->info('All phone numbers are already normalized.');

            return;
        }

        if ($dryRun) {
            $this->warn("[DRY RUN] {$changed} phone number(s) would be updated.");
        } else {
            $this->info("{$changed} phone number(s) normalized successfully.");
        }
    }
}
