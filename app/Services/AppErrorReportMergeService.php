<?php

namespace App\Services;

use App\Models\AppErrorReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AppErrorReportMergeService
{
    /**
     * Merge unchecked reports that have the same error signature.
     *
     * @return array{groups: int, reports: int}
     */
    public function mergeDuplicates(?int $mergedBy): array
    {
        return DB::transaction(function () use ($mergedBy): array {
            /** @var Collection<string, Collection<int, AppErrorReport>> $groups */
            $groups = AppErrorReport::query()
                ->whereNull('checked_at')
                ->whereNull('merged_into_id')
                ->orderByDesc('created_at')
                ->lockForUpdate()
                ->get()
                ->groupBy(fn (AppErrorReport $report): string => $this->signatureFor($report));

            $mergedGroups = 0;
            $mergedReports = 0;

            foreach ($groups as $reports) {
                if ($reports->count() < 2) {
                    continue;
                }

                $primaryReport = $reports->first();
                $duplicateReports = $reports->skip(1);
                $mergedAt = now();

                AppErrorReport::query()
                    ->whereKey($duplicateReports->modelKeys())
                    ->update([
                        'merged_into_id' => $primaryReport->getKey(),
                        'merged_at' => $mergedAt,
                        'merged_by' => $mergedBy,
                    ]);

                $primaryReport->update([
                    'occurrence_count' => $reports->sum('occurrence_count'),
                    'first_occurred_at' => $this->firstOccurredAt($reports),
                    'last_occurred_at' => $this->lastOccurredAt($reports),
                    'merged_at' => $mergedAt,
                    'merged_by' => $mergedBy,
                ]);

                $mergedGroups++;
                $mergedReports += $duplicateReports->count();
            }

            return [
                'groups' => $mergedGroups,
                'reports' => $mergedReports,
            ];
        });
    }

    private function signatureFor(AppErrorReport $report): string
    {
        return hash('sha256', json_encode([
            'type' => $report->type->value,
            'custom_type' => $this->normalize($report->custom_type),
            'error_code' => $this->normalize($report->error_code),
            'message' => $this->normalize($report->message),
            'source' => $this->normalize($report->source),
            'stack_origin' => $this->stackOrigin($report->stack_trace),
            'api_method' => $this->normalize($report->api_method),
            'api_url' => $this->normalize($report->api_url),
            'api_status_code' => $report->api_status_code,
        ], JSON_THROW_ON_ERROR));
    }

    /**
     * @param  Collection<int, AppErrorReport>  $reports
     */
    private function firstOccurredAt(Collection $reports): ?CarbonInterface
    {
        return $reports
            ->map(fn (AppErrorReport $report): ?CarbonInterface => $report->first_occurred_at ?? $report->occurred_at ?? $report->created_at)
            ->filter()
            ->sort()
            ->first();
    }

    /**
     * @param  Collection<int, AppErrorReport>  $reports
     */
    private function lastOccurredAt(Collection $reports): ?CarbonInterface
    {
        return $reports
            ->map(fn (AppErrorReport $report): ?CarbonInterface => $report->last_occurred_at ?? $report->occurred_at ?? $report->created_at)
            ->filter()
            ->sortDesc()
            ->first();
    }

    private function stackOrigin(?string $stackTrace): ?string
    {
        if ($stackTrace === null) {
            return null;
        }

        return $this->normalize(implode(PHP_EOL, array_slice(array_filter(
            preg_split('/\R/', $stackTrace) ?: [],
            fn (string $line): bool => filled($line),
        ), 0, 3)));
    }

    private function normalize(?string $value): ?string
    {
        if (blank($value)) {
            return null;
        }

        return mb_strtolower((string) preg_replace('/\s+/', ' ', trim($value)));
    }
}
