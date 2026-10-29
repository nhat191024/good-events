<?php

namespace App\Console\Commands;

use App\Models\PartnerCategory;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Collection;

class SoftDeleteServicesForTrashedPartnerCategories extends Command
{
    protected $signature = 'partner-services:delete-for-trashed-categories
                            {--chunk=500 : Number of categories and services to process per chunk}
                            {--dry-run : Report matching services without deleting them}';

    protected $description = 'Soft delete partner services belonging to soft-deleted partner categories';

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
        $processedCategories = 0;
        $deletedServices = 0;

        PartnerCategory::onlyTrashed()
            ->select('id')
            ->chunkById($chunkSize, function (Collection $categories) use ($chunkSize, $dryRun, &$processedCategories, &$deletedServices): void {
                foreach ($categories as $category) {
                    $deletedServices += $dryRun
                        ? $category->partnerServices()->count()
                        : $category->softDeletePartnerServices($chunkSize);

                    $processedCategories++;
                }
            });

        $action = $dryRun ? 'Would soft delete' : 'Soft deleted';
        $this->info("{$action} {$deletedServices} partner service(s) across {$processedCategories} soft-deleted partner category/categories.");

        return self::SUCCESS;
    }
}
