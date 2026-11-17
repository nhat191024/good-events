<?php

namespace App\Filament\Admin\Resources\RevenueStatistics\Pages;

use App\Filament\Admin\Resources\RevenueStatistics\RevenueStatisticResource;
use App\Services\InvoiceLedgerExporter;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListRevenueStatistics extends ListRecords
{
    protected static string $resource = RevenueStatisticResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportInvoiceLedger')
                ->label(__('admin/revenue_statistic.export.label'))
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('success')
                ->action(fn (): StreamedResponse => app(InvoiceLedgerExporter::class)->download(
                    $this->getFilteredTableQuery(),
                    'bang-ke-hoa-don-'.now()->format('Ymd-His').'.xlsx',
                )),
        ];
    }
}
