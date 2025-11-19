<?php

namespace App\Filament\Admin\Resources\Admins\Pages;

use App\Models\User;
use App\Filament\Admin\Resources\Admins\AdminsResource;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListAdmins extends ListRecords
{
    protected static string $resource = AdminsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->mutateDataUsing(function (array $data): array {
                    $data['country_code'] = '0';
                    $data['phone'] = '0000000000';
                    $data['email_verified_at'] = now();
                    return $data;
                })
                ->after(function (User $record, array $data) {
                    if (isset($data['role'])) {
                        $record->syncRoles([$data['role']]);
                    }
                }),
        ];
    }
}
