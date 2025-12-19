<?php

namespace App\Filament\Admin\Resources\Reports\Tables;

use Cmgmyr\Messenger\Models\Participant;
use Cmgmyr\Messenger\Models\Thread;

use Filament\Actions\BulkActionGroup;

use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

use App\Enum\ReportStatus;

class ReportsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label(__('admin/report.fields.id'))
                    ->sortable(),
                TextColumn::make('title')
                    ->label(__('admin/report.fields.title'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label(__('admin/report.fields.user'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('reportedUser.name')
                    ->label(__('admin/report.fields.reportedUser'))
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                    ->sortable(),
                TextColumn::make('reportedBill.code')
                    ->label(__('admin/report.fields.reportedBill'))
                    ->searchable()
                    ->formatStateUsing(fn($state) => $state ?? 'N/A')
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('admin/report.fields.status'))
                    ->formatStateUsing(fn($state) => $state->label())
                    ->color(fn($state) => match ($state) {
                        ReportStatus::PENDING => 'warning',
                        ReportStatus::REVIEWED => 'success',
                        ReportStatus::RESOLVED => 'danger',
                        default => 'secondary',
                    })
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('admin/report.fields.created_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('updated_at')
                    ->label(__('admin/report.fields.updated_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                Action::make('changeStatus')
                    ->label(__('admin/report.actions.change_status'))
                    ->icon('heroicon-o-pencil-square')
                    ->schema([
                        Select::make('status')
                            ->label(__('admin/report.fields.status'))
                            ->options(collect(ReportStatus::cases())->mapWithKeys(fn($status) => [$status->value => $status->label()]))
                            ->required(),
                    ])
                    ->fillForm(fn($record) => ['status' => $record->status])
                    ->action(fn($record, array $data) => $record->update(['status' => $data['status']]))
                    ->color('primary'),
                Action::make('createChat')
                    ->label(__('admin/report.actions.create_chat'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->tooltip(__('admin/report.helpers.create_chat'))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $thread = Thread::create([
                            'subject' => 'Báo cáo số ' . $record->id,
                        ]);

                        Participant::create([
                            'thread_id' => $thread->id,
                            'user_id' => $record->user_id,
                            'last_read' => null,
                        ]);

                        Participant::create([
                            'thread_id' => $thread->id,
                            'user_id' => auth()->id(),
                            'last_read' => now(),
                        ]);

                        $record->update(['thread_id' => $thread->id]);
                        $record->save();

                        redirect()->route('filament.admin.pages.chat', ['chat' => $thread->id]);
                    })
                    ->color('success')
                    ->visible(fn($record) => $record->thread_id === null),
                Action::make('viewChat')
                    ->label(__('admin/report.actions.view_chat'))
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->tooltip(__('admin/report.helpers.view_chat'))
                    ->action(function ($record) {
                        redirect()->route('filament.admin.pages.chat', ['chat' => $record->thread_id]);
                    })
                    ->color('secondary')
                    ->visible(fn($record) => $record->thread_id !== null),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    //
                ]),
            ]);
    }
}
