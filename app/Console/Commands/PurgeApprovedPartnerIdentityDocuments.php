<?php

namespace App\Console\Commands;

use App\Models\PartnerProfile;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PurgeApprovedPartnerIdentityDocuments extends Command
{
    protected $signature = 'partner-profiles:purge-approved-identity-documents
                            {--chunk=100 : Number of partner profiles to process per chunk}
                            {--dry-run : Report records and files without deleting them}
                            {--force : Delete data without an interactive confirmation}';

    protected $description = 'Delete identity documents for approved partner profiles';

    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $dryRun = (bool) $this->option('dry-run');

        if ($chunkSize < 1) {
            $this->error('The --chunk option must be at least 1.');

            return self::FAILURE;
        }

        $profiles = $this->approvedProfilesQuery();
        $profileCount = $profiles->count();

        if ($profileCount === 0) {
            $this->info('No approved partner profiles with identity data were found.');

            return self::SUCCESS;
        }

        if (! $dryRun && ! $this->option('force') && ! $this->confirm(
            "Delete identity data for {$profileCount} approved partner profile(s)?",
        )) {
            $this->warn('Operation cancelled.');

            return self::SUCCESS;
        }

        $stats = [
            'profiles' => 0,
            'files' => 0,
        ];

        $profiles->chunkById($chunkSize, function (Collection $partnerProfiles) use ($dryRun, &$stats): void {
            foreach ($partnerProfiles as $partnerProfile) {
                $documentPaths = $this->documentPaths($partnerProfile);

                $stats['profiles']++;
                $stats['files'] += count($documentPaths);

                if ($dryRun) {
                    continue;
                }

                foreach ($documentPaths as $documentPath) {
                    Storage::disk('local')->delete($documentPath);
                    Storage::disk('public')->delete($documentPath);
                }

                $partnerProfile->update([
                    'identity_card_number' => null,
                    'selfie_image' => null,
                    'front_identity_card_image' => null,
                    'back_identity_card_image' => null,
                ]);
            }
        });

        $action = $dryRun ? 'Would purge' : 'Purged';
        $this->info("{$action} identity data for {$stats['profiles']} approved partner profile(s).");
        $this->line(($dryRun ? 'Would delete' : 'Deleted') . " {$stats['files']} identity image file(s).");

        return self::SUCCESS;
    }

    private function approvedProfilesQuery(): Builder
    {
        return PartnerProfile::query()
            ->where('is_legit', true)
            ->where(function (Builder $query): void {
                $query
                    ->whereNotNull('identity_card_number')
                    ->orWhereNotNull('selfie_image')
                    ->orWhereNotNull('front_identity_card_image')
                    ->orWhereNotNull('back_identity_card_image');
            })
            ->orderBy('id');
    }

    /**
     * @return list<string>
     */
    private function documentPaths(PartnerProfile $partnerProfile): array
    {
        return collect([
            $partnerProfile->selfie_image,
            $partnerProfile->front_identity_card_image,
            $partnerProfile->back_identity_card_image,
        ])
            ->filter(fn (?string $path): bool => filled($path) && ! Str::startsWith($path, ['http://', 'https://']))
            ->map(fn (string $path): string => Str::after(
                ltrim(Str::before($path, '?'), '/'),
                'storage/',
            ))
            ->unique()
            ->values()
            ->all();
    }
}
