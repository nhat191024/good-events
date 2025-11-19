<?php

namespace App\Filament\Admin\Resources\EventCategories\Resources\PartnerCategories\Schemas;

use App\Models\PartnerCategory;

use Filament\Schemas\Schema;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;

class PartnerCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('admin/partnerCategory.fields.name'))
                    ->required(),
                Select::make('parent_id')
                    ->label(__('admin/partnerCategory.fields.parent_id'))
                    ->searchable()
                    ->getSearchResultsUsing(
                        fn(string $search): array =>
                        PartnerCategory::query()
                            ->whereNull('parent_id')
                            ->where('name', 'like', "%{$search}%")
                            ->limit(50)
                            ->pluck('name', 'id')
                            ->toArray()
                    )
                    ->getOptionLabelUsing(
                        callback: fn($value): ?string =>
                        PartnerCategory::find($value)?->name
                    )
                    ->default(fn($livewire) => $livewire->getParentRecord()?->id)
                    ->required(),
                TextInput::make('slug')
                    ->label(__('admin/partnerCategory.fields.slug'))
                    ->placeholder(__('admin/partnerCategory.placeholders.slug'))
                    ->disabled()
                    ->columnSpanFull(),
                TextInput::make('min_price')
                    ->label(__('admin/partnerCategory.fields.min_price'))
                    ->numeric()
                    ->required(),
                TextInput::make('max_price')
                    ->label(__('admin/partnerCategory.fields.max_price'))
                    ->numeric()
                    ->required(),
                SpatieMediaLibraryFileUpload::make('images')
                    ->label(__('admin/partnerCategory.fields.image'))
                    ->collection('images')
                    ->image()
                    ->maxFiles(1)
                    ->visibility('public')
                    ->columnSpanFull(),
                RichEditor::make('description')
                    ->label(__('admin/partnerCategory.fields.description'))
                    ->columnSpanFull(),
            ]);
    }
}
