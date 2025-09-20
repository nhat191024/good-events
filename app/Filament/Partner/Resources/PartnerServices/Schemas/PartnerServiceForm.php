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
                    ->label('Danh mục dịch vụ')
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
                        'unique' => 'Bạn đã đăng ký dịch vụ cho danh mục này rồi. Vui lòng chọn danh mục khác.',
                    ]),

                Repeater::make('serviceMedia')
                    ->label('Video giới thiệu dịch vụ')
                    ->relationship()
                    ->disabled(fn($operation) => $operation === 'edit')
                    ->schema([
                        TextInput::make('name')
                            ->label('Tên video')
                            ->required()
                            ->placeholder('VD: Video giới thiệu dịch vụ chụp ảnh cưới'),
                        TextInput::make('url')
                            ->label('Link video')
                            ->url()
                            ->required()
                            ->placeholder('https://youtube.com/watch?v=...'),
                        Textarea::make('description')
                            ->label('Mô tả')
                            ->placeholder('Mô tả ngắn về video này')
                            ->rows(2),
                    ])
                    ->collapsible()
                    ->itemLabel(fn(array $state): ?string => $state['name'] ?? 'Video chưa có tên')
                    ->addActionLabel('Thêm video')
                    ->reorderableWithButtons()
                    ->cloneable()
                    ->columnSpanFull()
                    ->helperText('Thêm các video giới thiệu dịch vụ của bạn để admin duyệt. Chỉ có thể thêm khi tạo mới.'),

            ]);
    }
}
