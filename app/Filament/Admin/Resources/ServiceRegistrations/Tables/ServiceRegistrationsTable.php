<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;

use Filament\Actions\Action;
use Filament\Actions\EditAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;

use App\Enum\PartnerServiceStatus;
use App\Models\PartnerService;

class ServiceRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('category.name')
                    ->label(__('admin/partnerService.fields.category'))
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label(__('admin/partnerService.fields.user'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('admin/partnerService.fields.status'))
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state->label())
                    ->color(fn($state) => match ($state) {
                        PartnerServiceStatus::PENDING => 'warning',
                        PartnerServiceStatus::APPROVED => 'success',
                        PartnerServiceStatus::REJECTED => 'danger',
                        default => 'secondary',
                    })
                    ->badge(),
                TextColumn::make('deleted_at')
                    ->label(__('admin/partnerService.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/partnerService.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/partnerService.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                Action::make('viewVideos')
                    ->label(__('admin/partnerService.actions.view_videos'))
                    ->icon('heroicon-o-video-camera')
                    ->color('info')
                    ->modalHeading(fn(PartnerService $record) => 'Service Videos - ' . $record->category->name)
                    ->modalContent(fn(PartnerService $record) => view('filament.admin.modals.service-media', [
                        'mediaItems' => $record->serviceMedia,
                    ]))
                    ->modalWidth('5xl')
                    ->slideOver()
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('approve')
                    ->label(__('admin/partnerService.actions.approve'))
                    ->color('success')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->visible(fn(PartnerService $record) => in_array($record->status, [PartnerServiceStatus::PENDING, PartnerServiceStatus::REJECTED]))
                    ->action(function (PartnerService $record) {
                        $record->update(['status' => PartnerServiceStatus::APPROVED]);
                    })
                    ->successNotificationTitle(__('admin/partnerService.notifications.approved'))
                    ->modalHeading(__('admin/partnerService.modals.approve.heading'))
                    ->modalDescription(__('admin/partnerService.modals.approve.description')),
                Action::make('reject')
                    ->label(__('admin/partnerService.actions.reject'))
                    ->color('danger')
                    ->icon('heroicon-o-x-circle')
                    ->requiresConfirmation()
                    ->visible(fn(PartnerService $record) => in_array($record->status, [PartnerServiceStatus::PENDING, PartnerServiceStatus::APPROVED]))
                    ->action(function (PartnerService $record) {
                        $record->update(['status' => PartnerServiceStatus::REJECTED]);
                    })
                    ->successNotificationTitle(__('admin/partnerService.notifications.rejected'))
                    ->modalHeading(__('admin/partnerService.modals.reject.heading'))
                    ->modalDescription(__('admin/partnerService.modals.reject.description')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
