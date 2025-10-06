<?php

namespace App\Filament\Admin\Resources\Cities\Pages;

use App\Filament\Admin\Resources\Cities\CityResource;
use App\Filament\Admin\Resources\Cities\Resources\Locations\LocationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;

class ManageWard extends ManageRelatedRecords
{
    protected static string $resource = CityResource::class;

    protected static string $relationship = 'wards';

    protected static ?string $relatedResource = LocationResource::class;

    public function getTitle(): string
    {
        return __('admin/location.manage_wards', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            CityResource::getIndexUrl() => __('admin/location.singular'),
            $this->getRecord()->name,
            __('global.list'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->withTrashed())
            ->headerActions([
                // CreateAction::make(),
            ]);
    }
}
