<?php

namespace App\Filament\Admin\Resources\EventOrganizationGuides\Tables;

use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;

use Filament\Tables\Filters\TrashedFilter;

class EventOrganizationGuidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin/blog.fields.id'))
                    ->numeric()
                    ->sortable(),
                SpatieMediaLibraryImageColumn::make('images')
                    ->label(__('admin/blog.fields.thumbnail'))
                    ->collection('thumbnail')
                    ->circular()
                    ->imageSize(50),
                TextColumn::make('title')
                    ->label(__('admin/blog.fields.title'))
                    ->searchable(),
                TextColumn::make('author.name')
                    ->label(__('admin/blog.fields.author_id'))
                    ->searchable(),
                TextColumn::make('category.name')
                    ->label(__('admin/blog.fields.category_id')),
                SpatieTagsColumn::make('tags')
                    ->label(__('admin/blog.fields.tags'))
                    ->alignCenter(),
                TextColumn::make('slug')
                    ->label(__('admin/blog.fields.slug'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label(__('admin/blog.fields.deleted_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label(__('admin/blog.fields.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('admin/blog.fields.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('view_video')
                    ->label('Xem video')
                    ->url(fn ($record) => $record->video_url)
                    ->openUrlInNewTab(),
                EditAction::make(),
                DeleteAction::make()
                    ->label(__('global.hidden')),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    // ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
