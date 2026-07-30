<?php

namespace App\Filament\Admin\Resources\AppErrorReports\Pages;

use App\Filament\Admin\Resources\AppErrorReports\AppErrorReportResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAppErrorReport extends ViewRecord
{
    protected static string $resource = AppErrorReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
