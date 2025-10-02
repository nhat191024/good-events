<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations\Pages;

use App\Filament\Admin\Resources\ServiceRegistrations\ServiceRegistrationResource;
use Filament\Resources\Pages\CreateRecord;

class CreateServiceRegistration extends CreateRecord
{
    protected static string $resource = ServiceRegistrationResource::class;
}
