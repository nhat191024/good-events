<?php

namespace App\Filament\Admin\Resources\PartnerBills\Schemas;

use App\Enum\PartnerBillStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Schema;

class PartnerBillForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->required(),
                TextInput::make('address')
                    ->required(),
                TextInput::make('phone')
                    ->tel()
                    ->required(),
                DatePicker::make('date'),
                TimePicker::make('start_time'),
                TimePicker::make('end_time'),
                TextInput::make('final_total')
                    ->numeric(),
                TextInput::make('event_id')
                    ->numeric(),
                TextInput::make('client_id')
                    ->numeric(),
                TextInput::make('partner_id')
                    ->numeric(),
                TextInput::make('category_id')
                    ->numeric(),
                Textarea::make('note')
                    ->columnSpanFull(),
                Select::make('status')
                    ->options(PartnerBillStatus::class)
                    ->required(),
                TextInput::make('thread_id')
                    ->numeric(),
            ]);
    }
}
