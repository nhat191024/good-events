<?php

namespace App\Filament\Admin\Resources\AppErrorReports\Tables;

use App\Enum\AppErrorSeverity;
use App\Enum\AppErrorType;
use App\Models\AppErrorReport;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AppErrorReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Loại lỗi')
                    ->badge()
                    ->sortable(),
                TextColumn::make('severity')
                    ->label('Mức độ')
                    ->badge()
                    ->sortable(),
                TextColumn::make('message')
                    ->label('Nội dung')
                    ->limit(70)
                    ->tooltip(fn (AppErrorReport $record): string => $record->message)
                    ->searchable(),
                TextColumn::make('error_code')
                    ->label('Mã lỗi')
                    ->placeholder('—')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('api_status_code')
                    ->label('HTTP')
                    ->badge()
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Người dùng')
                    ->placeholder('Khách')
                    ->searchable(),
                TextColumn::make('app_version')
                    ->label('Phiên bản app')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('platform')
                    ->label('Nền tảng')
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('occurred_at')
                    ->label('Xảy ra lúc')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Gửi lúc')
                    ->dateTime('d/m/Y H:i:s')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Loại lỗi')
                    ->options(AppErrorType::class),
                SelectFilter::make('severity')
                    ->label('Mức độ')
                    ->options(AppErrorSeverity::class),
                SelectFilter::make('platform')
                    ->label('Nền tảng')
                    ->options(fn (): array => AppErrorReport::query()
                        ->whereNotNull('platform')
                        ->distinct()
                        ->orderBy('platform')
                        ->pluck('platform', 'platform')
                        ->all()),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
