<?php

namespace App\Filament\Partner\Resources\PartnerServices\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rule;

class PartnerServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('category_id')
                    ->label(__('partner/service.label.service_list'))
                    ->relationship('category', 'name', fn($query) => $query->whereNull('parent_id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->rules([
                        function ($record) {
                            return Rule::unique('partner_services', 'category_id')
                                ->where('user_id', auth()->id())
                                ->ignore($record?->id);
                        }
                    ])
                    ->validationMessages([
                        'unique' => __('partner/service.helper.service_unique'),
                    ]),

                Repeater::make('serviceMedia')
                    ->label(__('partner/service.label.service_media'))
                    ->relationship()
                    ->disabled(fn($operation) => $operation === 'edit')
                    ->schema([
                        TextInput::make('name')
                            ->label(__('partner/service.label.video_name'))
                            ->required()
                            ->placeholder(__('partner/service.placeholder.video_name')),
                        TextInput::make('url')
                            ->label(__('partner/service.label.video_url'))
                            ->url()
                            ->required()
                            ->placeholder(__('partner/service.placeholder.video_url')),
                        Textarea::make('description')
                            ->label(__('partner/service.label.video_description'))
                            ->placeholder(__('partner/service.placeholder.video_description'))
                            ->rows(2),
                    ])
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? __('partner/service.video_no_name'))
                    ->addActionLabel(__('partner/service.button.add_video'))
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->columnSpanFull()
                    ->helperText(__('partner/service.helper.service_media')),

            ]);
    }
}
