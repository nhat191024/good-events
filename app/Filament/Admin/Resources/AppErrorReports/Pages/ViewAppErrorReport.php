<?php

namespace App\Filament\Admin\Resources\AppErrorReports\Pages;

use App\Filament\Admin\Resources\AppErrorReports\AppErrorReportResource;
use App\Models\AppErrorReport;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Js;

class ViewAppErrorReport extends ViewRecord
{
    protected static string $resource = AppErrorReportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('copyAsMarkdown')
                ->label('Copy dưới dạng README')
                ->icon(Heroicon::OutlinedClipboardDocument)
                ->color('gray')
                ->alpineClickHandler(function (): string {
                    $markdown = $this->buildAgentMarkdown($this->getRecord());

                    return 'window.navigator.clipboard.writeText('.Js::from($markdown).'); '
                        .'$tooltip('.Js::from('Đã sao chép nội dung lỗi.').', { theme: $store.theme });';
                }),
            DeleteAction::make(),
        ];
    }

    private function buildAgentMarkdown(AppErrorReport $record): string
    {
        $type = $record->type->value;
        $severity = $record->severity->value;
        $occurredAt = $record->occurred_at?->format('Y-m-d H:i:s P') ?? 'Không xác định';
        $reportedAt = $record->created_at?->format('Y-m-d H:i:s P') ?? 'Không xác định';
        $user = $record->user;

        $sections = [
            '# Báo cáo lỗi ứng dụng',
            '',
            '## Tóm tắt',
            '',
            "- **Report ID:** `{$record->uuid}`",
            "- **Loại lỗi:** `{$type}`",
            "- **Mức độ:** `{$severity}`",
            '- **Mã lỗi:** '.($record->error_code ? "`{$record->error_code}`" : 'Không có'),
            "- **Nguồn:** {$record->source}",
            "- **Xảy ra lúc:** {$occurredAt}",
            "- **Gửi lên lúc:** {$reportedAt}",
            '',
            '## Nội dung lỗi',
            '',
            $record->message,
            '',
            '## Người dùng và thiết bị',
            '',
            '- **User ID:** '.($user?->getKey() ?? 'Khách'),
            '- **Tên:** '.($user?->name ?? 'Không xác định'),
            '- **Email:** '.($user?->email ?? 'Không xác định'),
            '- **Phiên bản app:** '.($record->app_version ?? 'Không xác định'),
            '- **Nền tảng:** '.($record->platform ?? 'Không xác định'),
            '- **Phiên bản OS:** '.($record->os_version ?? 'Không xác định'),
            '- **Thiết bị:** '.($record->device_model ?? 'Không xác định'),
            '- **IP:** '.($record->ip_address ?? 'Không xác định'),
        ];

        if ($record->api_url !== null) {
            array_push(
                $sections,
                '',
                '## API',
                '',
                '- **Method:** '.($record->api_method ?? 'Không xác định'),
                '- **URL:** '.$record->api_url,
                '- **HTTP status:** '.($record->api_status_code ?? 'Không xác định'),
                '',
                '### Request',
                '',
                self::markdownCodeBlock($record->api_request),
                '',
                '### Response',
                '',
                self::markdownCodeBlock($record->api_response),
            );
        }

        array_push(
            $sections,
            '',
            '## Context',
            '',
            self::markdownCodeBlock($record->context),
            '',
            '## Stack trace',
            '',
            self::markdownCodeBlock($record->stack_trace, 'text'),
            '',
            '---',
            'Hãy phân tích nguyên nhân gốc, chỉ ra file/dòng có khả năng liên quan và đề xuất hoặc triển khai bản sửa phù hợp với convention của dự án.',
        );

        return implode(PHP_EOL, $sections);
    }

    private static function markdownCodeBlock(mixed $value, string $language = 'json'): string
    {
        if ($value === null || $value === '') {
            $content = 'Không có dữ liệu';
            $language = 'text';
        } elseif (is_string($value)) {
            $decodedValue = json_decode($value, true);
            $content = json_last_error() === JSON_ERROR_NONE
                ? self::encodeJson($decodedValue)
                : $value;
        } else {
            $content = self::encodeJson($value);
        }

        return "~~~~{$language}".PHP_EOL.$content.PHP_EOL.'~~~~';
    }

    private static function encodeJson(mixed $value): string
    {
        return json_encode(
            $value,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ) ?: 'Không thể hiển thị dữ liệu';
    }
}
