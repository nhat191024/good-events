<?php

namespace App\Filament\Admin\Resources\AppErrorReports\Pages;

use App\Filament\Admin\Resources\AppErrorReports\AppErrorReportResource;
use Filament\Resources\Pages\ListRecords;

class ListAppErrorReports extends ListRecords
{
    protected static string $resource = AppErrorReportResource::class;
}
