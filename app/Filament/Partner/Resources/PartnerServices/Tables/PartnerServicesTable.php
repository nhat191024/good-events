<?php

namespace App\Filament\Partner\Resources\PartnerServices\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

use App\Enum\PartnerServiceStatus;

class PartnerServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('partner\service.label.category'))
                    ->sortable(),
                TextColumn::make('serviceMedia')
                    ->label('Video giới thiệu')
                    ->formatStateUsing(function ($record) {
                        $count = $record->serviceMedia()->count();
                        if ($count === 0) {
                            return 'Chưa có video';
                        }
                        return "{$count} video";
                    })
                    ->action(
                        Action::make('viewVideos')
                            ->color('primary')
                            ->icon('heroicon-o-play')
                            ->modalHeading('Video giới thiệu dịch vụ')
                            ->modalContent(function ($record) {
                                $videos = $record->serviceMedia;
                                if ($videos->isEmpty()) {
                                    return view('filament.components.no-videos');
                                }
                                return view('filament.components.video-list', compact('videos'));
                            })
                            ->modalSubmitAction(false)
                            ->modalCancelActionLabel('Đóng')
                    )
                    ->searchable(false)
                    ->sortable(false),
                TextColumn::make('status')
                    ->label(__('partner\service.label.status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->colors([
                        'primary' => PartnerServiceStatus::PENDING,
                        'success' => PartnerServiceStatus::APPROVED,
                        'danger' => PartnerServiceStatus::REJECTED,
                    ])
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->label(__('partner\service.label.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('partner\service.label.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('partner\service.label.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make()
                    ->native(false)
                    ->default('only_trashed'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label(__('global.edit')),
                DeleteAction::make()
                    ->label(__('global.hidden')),
                RestoreAction::make()
                    ->label(__('global.show')),
            ])
            ->toolbarActions([
                // BulkActionGroup::make([
                //     DeleteBulkAction::make(),
                //     RestoreBulkAction::make(),
                // ]),
            ]);
    }
}
