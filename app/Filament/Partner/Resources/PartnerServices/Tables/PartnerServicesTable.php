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

use Filament\Notifications\Notification;

use App\Enum\PartnerServiceStatus;
use App\Filament\Partner\Resources\PartnerServices\Schemas\ServiceImagesForm;

class PartnerServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('partner/service.label.category'))
                    ->sortable(),
                TextColumn::make('service_media_count')
                    ->label(__('partner/service.label.videos'))
                    ->formatStateUsing(function ($state) {
                        if ($state === 0) {
                            return __('partner/service.no_video');
                        } else {
                            return "{$state} video";
                        }
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
                    ->label(__('partner/service.label.status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->colors([
                        'primary' => PartnerServiceStatus::PENDING,
                        'success' => PartnerServiceStatus::APPROVED,
                        'danger' => PartnerServiceStatus::REJECTED,
                    ])
                    ->searchable(),
                TextColumn::make('deleted_at')
                    ->label(__('partner/service.label.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('partner/service.label.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('partner/service.label.updated_at'))
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
                Action::make('manageImages')
                    ->label(__('partner/service.action.manage_images'))
                    ->icon('heroicon-o-photo')
                    ->color('info')
                    ->visible(fn($record) => $record->status === PartnerServiceStatus::APPROVED)
                    ->modalHeading(__('partner/service.modal.manage_images'))
                    ->modalWidth('2xl')
                    ->schema(fn() => ServiceImagesForm::configure(new \Filament\Schemas\Schema)->getComponents())
                    ->fillForm(fn($record) => [
                        'service_images' => $record,
                    ])
                    ->action(function ($record, array $data) {
                        // Data will be automatically saved by SpatieMediaLibraryFileUpload
                        Notification::make()
                            ->title(__('partner/service.notification.images_updated'))
                            ->success()
                            ->send();
                    }),
                EditAction::make()
                    ->label(__('global.edit'))
                    ->disabled(fn($record) => ! in_array($record->status, [PartnerServiceStatus::PENDING, PartnerServiceStatus::REJECTED]))
                    ->mutateDataUsing(function (array $data): array {
                        $data['user_id'] = auth()->id();

                        return $data;
                    }),
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
