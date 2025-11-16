<?php

namespace App\Filament\Admin\Resources\VocationalKnowledge;

use App\Models\Blog;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\VocationalKnowledge\Schemas\VocationalKnowledgeForm;
use App\Filament\Admin\Resources\VocationalKnowledge\Tables\VocationalKnowledgeTable;

use App\Filament\Admin\Resources\VocationalKnowledge\Pages\CreateVocationalKnowledge;
use App\Filament\Admin\Resources\VocationalKnowledge\Pages\EditVocationalKnowledge;
use App\Filament\Admin\Resources\VocationalKnowledge\Pages\ListVocationalKnowledge;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class VocationalKnowledgeResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::BLOG;

    public static function getNavigationLabel(): string
    {
        return __('admin/blog.singulars.vocational_knowledge');
    }

    public static function getModelLabel(): string
    {
        return __('admin/blog.plurals.vocational_knowledge');
    }

    public static function form(Schema $schema): Schema
    {
        return VocationalKnowledgeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return VocationalKnowledgeTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListVocationalKnowledge::route('/'),
            'create' => CreateVocationalKnowledge::route('/create'),
            'edit' => EditVocationalKnowledge::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'author', 'media'])
            ->whereType('vocational_knowledge');
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
