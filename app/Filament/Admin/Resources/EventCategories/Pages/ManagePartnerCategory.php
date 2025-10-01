<?php

namespace App\Filament\Admin\Resources\EventCategories\Pages;

use App\Filament\Admin\Resources\EventCategories\EventCategoryResource;
use App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\PartnerCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRelatedRecords;
use Filament\Tables\Table;

class ManagePartnerCategory extends ManageRelatedRecords
{
    protected static string $resource = EventCategoryResource::class;

    protected static string $relationship = 'children';

    protected static ?string $relatedResource = PartnerCategoryResource::class;

    public function getTitle(): string
    {
        return __('admin/partnerCategory.manage_event_categories', ['name' => $this->getRecord()->name]);
    }

    public function getBreadcrumbs(): array
    {
        return [
            EventCategoryResource::getIndexUrl() => __('admin/partnerCategory.plural'),
            $this->getRecord()->name,
            __('global.list'),
        ];
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->withExists('partnerServices')->withTrashed())
            ->headerActions([
                CreateAction::make()
                    ->label(__('admin/partnerCategory.create_partner_category')),
            ]);
    }
}
