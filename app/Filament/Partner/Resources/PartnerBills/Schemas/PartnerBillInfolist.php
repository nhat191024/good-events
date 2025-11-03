<?php

namespace App\Filament\Partner\Resources\PartnerBills\Schemas;

use App\Enum\PartnerBillStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class PartnerBillInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('partner/bill.basic_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('code')
                                    ->label(__('partner/bill.order_code')),
                                TextEntry::make('status')
                                    ->label(__('partner/bill.status'))
                                    ->badge()
                                    ->formatStateUsing(fn($state) => $state->label())
                                    ->colors([
                                        'primary' => PartnerBillStatus::PENDING,
                                        'success' => PartnerBillStatus::COMPLETED,
                                        'warning' => PartnerBillStatus::CONFIRMED,
                                        'danger' => PartnerBillStatus::EXPIRED,
                                        'danger' => PartnerBillStatus::CANCELLED,
                                    ]),
                            ]),
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('client.name')
                                    ->label(__('partner/bill.customer')),
                                TextEntry::make('category.name')
                                    ->label(__('partner/bill.service_category')),
                            ]),
                        TextEntry::make('event.name')
                            ->label(__('partner/bill.event_name')),
                    ]),

                Section::make(__('partner/bill.contact_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('phone')
                                    ->label(__('partner/bill.phone'))
                                    ->icon('heroicon-o-phone'),
                                TextEntry::make('address')
                                    ->label(__('partner/bill.address'))
                                    ->icon('heroicon-o-map-pin'),
                            ]),
                    ]),

                Section::make(__('partner/bill.time_information'))
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('date')
                                    ->label(__('partner/bill.organization_date'))
                                    ->date('d/m/Y')
                                    ->icon('heroicon-o-calendar-days'),
                                TextEntry::make('start_time')
                                    ->label(__('partner/bill.start_time_label'))
                                    ->time('H:i')
                                    ->badge()
                                    ->color('success')
                                    ->icon('heroicon-o-clock'),
                                TextEntry::make('end_time')
                                    ->label(__('partner/bill.end_time_label'))
                                    ->time('H:i')
                                    ->badge()
                                    ->color('danger')
                                    ->icon('heroicon-o-clock'),
                            ]),
                    ]),

                Section::make(__('partner/bill.payment_information'))
                    ->schema([
                        TextEntry::make('final_total')
                            ->label(__('partner/bill.total_amount_label'))
                            ->numeric()
                            ->formatStateUsing(fn($state) => $state ? number_format($state) . ' VND' : __('partner/bill.not_determined'))
                            ->size('lg')
                            ->weight('bold')
                            ->color('success'),
                    ]),

                Section::make(__('partner/bill.notes'))
                    ->schema([
                        TextEntry::make('note')
                            ->label(__('partner/bill.note_label'))
                            ->placeholder(__('partner/bill.no_note'))
                            ->html(),
                    ])
                    ->visible(fn($record) => !empty($record->note)),

                Section::make(__('partner/bill.system_information'))
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('created_at')
                                    ->label(__('partner/bill.created_date'))
                                    ->dateTime('d/m/Y H:i:s'),
                                TextEntry::make('updated_at')
                                    ->label(__('partner/bill.last_updated_at'))
                                    ->dateTime('d/m/Y H:i:s'),
                            ]),
                    ])
                    ->collapsible()
                    ->collapsed(),
            ]);
    }
}
