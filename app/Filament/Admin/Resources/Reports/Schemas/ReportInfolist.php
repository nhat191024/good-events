<?php

namespace App\Filament\Admin\Resources\Reports\Schemas;

use App\Enum\ReportStatus;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReportInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([


                Section::make(__('admin/report.infolist.report_details'))
                    ->columnSpan(2)
                    ->schema([
                        TextEntry::make('title')
                            ->label(__('admin/report.fields.title')),
                        TextEntry::make('status')
                            ->label(__('admin/report.fields.status'))
                            ->badge()
                            ->formatStateUsing(fn(ReportStatus $state) => $state->label())
                            ->color(fn(ReportStatus $state): string => match ($state) {
                                ReportStatus::PENDING => 'warning',
                                ReportStatus::REVIEWED => 'info',
                                ReportStatus::RESOLVED => 'success',
                            }),
                        TextEntry::make('description')
                            ->label(__('admin/report.fields.content'))
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make(__('admin/report.infolist.relationships'))
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label(__('admin/report.fields.user')),
                        TextEntry::make('reportedUser.name')
                            ->label(__('admin/report.fields.reportedUser')),
                        TextEntry::make('reportedBill.id')
                            ->label(__('admin/report.fields.reportedBill')),
                    ]),

                Section::make(__('admin/report.infolist.timestamps'))
                    ->columnSpan(1)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('admin/report.fields.created_at'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('admin/report.fields.updated_at'))
                            ->dateTime(),
                    ])->columns(1),

            ]);
    }
}
