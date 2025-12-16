<?php

namespace App\Filament\Admin\Resources\Users\Pages;

use App\Filament\Admin\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->label(__('global.ban'))
                ->modalHeading(__('admin/user.ban_title'))
                ->modalDescription(__('admin/user.ban_description'))
                ->modalSubmitActionLabel(__('global.ban'))
                ->successNotificationTitle(__('admin/user.ban_success_message')),
            RestoreAction::make(),
        ];
    }
}
