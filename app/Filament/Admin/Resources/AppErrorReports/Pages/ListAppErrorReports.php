<?php

namespace App\Filament\Admin\Resources\AppErrorReports\Pages;

use App\Filament\Admin\Resources\AppErrorReports\AppErrorReportResource;
use App\Services\AppErrorReportMergeService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;

class ListAppErrorReports extends ListRecords
{
    protected static string $resource = AppErrorReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('mergeDuplicates')
                ->label('Gộp lỗi trùng')
                ->icon(Heroicon::OutlinedRectangleStack)
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Gộp các báo cáo lỗi trùng?')
                ->modalDescription('Chỉ gộp các lỗi chưa kiểm tra có loại, nội dung, nguồn và thông tin kỹ thuật trùng khớp. Báo cáo gốc vẫn được lưu để truy vết.')
                ->modalSubmitActionLabel('Gộp lỗi')
                ->action(function (AppErrorReportMergeService $mergeService): void {
                    $result = $mergeService->mergeDuplicates(auth()->id());

                    if ($result['groups'] === 0) {
                        Notification::make()
                            ->title('Không tìm thấy báo cáo lỗi trùng phù hợp')
                            ->warning()
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->title("Đã gộp {$result['reports']} báo cáo vào {$result['groups']} nhóm lỗi")
                        ->success()
                        ->send();
                }),
        ];
    }
}
