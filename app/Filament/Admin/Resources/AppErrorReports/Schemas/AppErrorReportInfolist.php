<?php

namespace App\Filament\Admin\Resources\AppErrorReports\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AppErrorReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Thông tin lỗi')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('uuid')->label('Mã báo cáo')->copyable()->columnSpan(2),
                        TextEntry::make('type')->label('Loại')->badge(),
                        TextEntry::make('severity')->label('Mức độ')->badge(),
                        TextEntry::make('custom_type')->label('Loại khác')->placeholder('—'),
                        TextEntry::make('error_code')->label('Mã lỗi')->placeholder('—'),
                        TextEntry::make('source')->label('Nguồn')->placeholder('—')->columnSpan(2),
                        TextEntry::make('message')->label('Nội dung')->columnSpanFull(),
                        TextEntry::make('stack_trace')
                            ->label('Stack trace')
                            ->placeholder('—')
                            ->fontFamily('mono')
                            ->columnSpanFull(),
                    ]),
                Section::make('API')
                    ->columns(4)
                    ->collapsed(fn ($record): bool => $record->api_url === null)
                    ->schema([
                        TextEntry::make('api_method')->label('Method')->badge()->placeholder('—'),
                        TextEntry::make('api_status_code')->label('HTTP status')->badge()->placeholder('—'),
                        TextEntry::make('api_url')->label('URL')->copyable()->placeholder('—')->columnSpan(2),
                        self::jsonEntry('api_request', 'Request'),
                        self::jsonEntry('api_response', 'Response'),
                    ]),
                Section::make('Ngữ cảnh')
                    ->schema([
                        self::jsonEntry('context', 'Context')->columnSpanFull(),
                    ]),
                Section::make('Thiết bị & người gửi')
                    ->columns(4)
                    ->schema([
                        TextEntry::make('user.name')->label('Người dùng')->placeholder('Khách'),
                        TextEntry::make('user.email')->label('Email')->placeholder('—'),
                        TextEntry::make('app_version')->label('Phiên bản app')->placeholder('—'),
                        TextEntry::make('platform')->label('Nền tảng')->placeholder('—'),
                        TextEntry::make('os_version')->label('Phiên bản OS')->placeholder('—'),
                        TextEntry::make('device_model')->label('Thiết bị')->placeholder('—'),
                        TextEntry::make('ip_address')->label('IP')->placeholder('—'),
                        TextEntry::make('occurred_at')->label('Xảy ra lúc')->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('user_agent')->label('User agent')->placeholder('—')->columnSpanFull(),
                        TextEntry::make('created_at')->label('Gửi lúc')->dateTime('d/m/Y H:i:s'),
                        TextEntry::make('checked_at')
                            ->label('Đã kiểm tra lúc')
                            ->dateTime('d/m/Y H:i:s')
                            ->placeholder('Chưa kiểm tra'),
                        TextEntry::make('checkedBy.name')
                            ->label('Người kiểm tra')
                            ->placeholder('—'),
                    ]),
            ]);
    }

    private static function jsonEntry(string $name, string $label): TextEntry
    {
        return TextEntry::make($name)
            ->label($label)
            ->formatStateUsing(fn (mixed $state): string => self::formatJsonState($state))
            ->fontFamily('mono')
            ->columnSpan(2);
    }

    private static function formatJsonState(mixed $state): string
    {
        if ($state === null || $state === '') {
            return '—';
        }

        if (is_string($state)) {
            $decodedState = json_decode($state, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $state = $decodedState;
            } else {
                return $state;
            }
        }

        return json_encode(
            $state,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        ) ?: '—';
    }
}
