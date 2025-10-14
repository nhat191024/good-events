<?php

namespace App\Filament\Partner\Resources\PartnerServices\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class ServiceImagesForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                SpatieMediaLibraryFileUpload::make('service_images')
                    ->label(__('partner/service.label.service_images'))
                    ->collection('service_images')
                    ->image()
                    ->multiple()
                    ->maxFiles(10)
                    ->reorderable()
                    ->columnSpanFull()
                    ->helperText(__('partner/service.helper.service_images')),
            ]);
    }
}
