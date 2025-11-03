<?php

namespace App\Filament\Admin\Resources\ServiceRegistrations\Schemas;

use App\Enum\PartnerServiceStatus;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;

use Filament\Schemas\Schema;

class ServiceRegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('category_id')
                    ->required()
                    ->numeric(),
                TextInput::make('user_id')
                    ->required()
                    ->numeric(),
                Select::make('status')
                    ->options(PartnerServiceStatus::class)
                    ->required(),
            ]);
    }
}
