<?php

namespace App\Filament\Admin\Resources\Blogs;

use App\Models\Blog;

use BackedEnum;
use UnitEnum;

use App\Filament\Admin\Resources\Blogs\Schemas\BlogForm;
use App\Filament\Admin\Resources\Blogs\Tables\BlogsTable;

use App\Filament\Admin\Resources\Blogs\Pages\CreateBlog;
use App\Filament\Admin\Resources\Blogs\Pages\EditBlog;
use App\Filament\Admin\Resources\Blogs\Pages\ListBlogs;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

use App\Enum\FilamentNavigationGroup;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;
    protected static string|UnitEnum|null $navigationGroup = FilamentNavigationGroup::PRODUCTS;

    public static function getModelLabel(): string
    {
        return __('admin/blog.singular');
    }

    public static function form(Schema $schema): Schema
    {
        return BlogForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return BlogsTable::configure($table);
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
            'index' => ListBlogs::route('/'),
            'create' => CreateBlog::route('/create'),
            'edit' => EditBlog::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['category', 'author', 'media']);
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
