<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations\Pages;

use App\Filament\Admin\Resources\ServiceRegistrations\ServiceRegistrationResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditServiceRegistration extends EditRecord
{
    protected static string $resource = ServiceRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
