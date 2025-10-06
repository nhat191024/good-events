<?php

namespace App\Filament\Admin\Resources\Vouchers\Schemas;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class VoucherForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label(__('admin/voucher.fields.label.code'))
                    ->placeholder(__('admin/voucher.fields.placeholder.code'))
                    ->required()
                    ->maxLength(32)
                    ->unique(ignoreRecord: true),
                TextInput::make('data.discount_percent')
                    ->label(__('admin/voucher.fields.label.discount_percent'))
                    ->placeholder(__('admin/voucher.fields.placeholder.discount_percent'))
                    ->helperText(__('admin/voucher.fields.helper.discount_percent'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->maxValue(100)
                    ->suffix('%'),
                TextInput::make('data.max_discount_amount')
                    ->label(__('admin/voucher.fields.label.max_discount_amount'))
                    ->placeholder(__('admin/voucher.fields.placeholder.max_discount_amount'))
                    ->helperText(__('admin/voucher.fields.helper.max_discount_amount'))
                    ->numeric()
                    ->minValue(0)
                    ->prefix('₫'),
                TextInput::make('data.min_order_amount')
                    ->label(__('admin/voucher.fields.label.min_order_amount'))
                    ->placeholder(__('admin/voucher.fields.placeholder.min_order_amount'))
                    ->helperText(__('admin/voucher.fields.helper.min_order_amount'))
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('₫'),
                TextInput::make('data.usage_limit')
                    ->label(__('admin/voucher.fields.label.usage_limit'))
                    ->placeholder(__('admin/voucher.fields.placeholder.usage_limit'))
                    ->helperText(__('admin/voucher.fields.helper.usage_limit'))
                    ->numeric()
                    ->minValue(1)
                    ->required(fn($get) => !$get('data.is_unlimited'))
                    ->disabled(fn($get) => $get('data.is_unlimited'))
                    ->dehydrated(fn($get) => !$get('data.is_unlimited')),
                Select::make('model_type')
                    ->label(__('admin/voucher.fields.label.model_type'))
                    ->placeholder(__('admin/voucher.fields.placeholder.model_type'))
                    ->options([
                        'App\Models\PartnerProfile' => __('admin/voucher.fields.select.model_type.partner'),
                    ])
                    ->required()
                    ->default('App\Models\PartnerProfile')
                    ->native(false)
                    ->reactive(),
                Checkbox::make('data.is_unlimited')
                    ->label(__('admin/voucher.fields.label.is_unlimited'))
                    ->default(false)
                    ->live()
                    ->afterStateUpdated(fn($state, $set) => $state ? $set('data.usage_limit', null) : null),

                Grid::make(2)
                    ->columnSpanFull()
                    ->schema([
                        DateTimePicker::make('data.starts_at')
                            ->label(__('admin/voucher.fields.label.starts_at'))
                            ->placeholder(__('admin/voucher.fields.placeholder.starts_at'))
                            ->required()
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->default(now()),

                        DateTimePicker::make('expires_at')
                            ->label(__('admin/voucher.fields.label.expires_at'))
                            ->placeholder(__('admin/voucher.fields.placeholder.expires_at'))
                            ->native(false)
                            ->displayFormat('d/m/Y H:i')
                            ->after('data.starts_at'),
                    ]),
            ]);
    }
}
