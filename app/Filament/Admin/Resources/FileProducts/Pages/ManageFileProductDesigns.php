<?php

namespace App\Filament\Admin\Resources\FileProducts\Pages;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;
use App\Models\FileProduct;
use Filament\Actions\Action;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Support\Enums\IconSize;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

class ManageFileProductDesigns extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = FileProductResource::class;

    protected string $view = 'filament.admin.resources.file-products.pages.manage-designs';

    public ?array $data = [];

    public FileProduct $record;

    public function mount(FileProduct $record): void
    {
        $this->form->fill([
            'designs' => $record->media
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Design Files')
                    ->description('Upload và quản lý các file design cho sản phẩm này')
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('designs')
                            ->label('Ảnh sản phẩm')
                            ->collection('designs')
                            ->multiple()
                            ->reorderable()
                            ->downloadable()
                            ->openable()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                null,
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->maxFiles(20)
                            ->maxSize(1024 * 500) // 500MB
                            ->helperText('Có thể upload tối đa 20 ảnh, mỗi ảnh tối đa 500MB. Hỗ trợ mọi định dạng.')
                            ->acceptedFileTypes([
                                'image/*',
                                'application/pdf',
                                // Compression
                                'application/zip',
                                'application/x-zip-compressed',
                                'multipart/x-zip',
                                'application/x-rar-compressed',
                                'application/vnd.rar',
                                // Adobe Specific
                                'image/vnd.adobe.photoshop', // .psd
                                'application/postscript',     // .ai, .eps
                                'application/x-indesign',     // .indd
                            ])
                            ->columnSpanFull(),
                    ]),
            ])
            ->statePath('data')
            ->model($this->record);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Quay lại')
                ->icon('heroicon-o-arrow-left')
                ->url(FileProductResource::getUrl('index'))
                ->color('gray'),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Notification::make()
            ->success()
            ->title('Đã cập nhật ảnh thành công!')
            ->send();
    }

    public function getTitle(): string
    {
        return 'Quản lý ảnh - ' . $this->record->name;
    }

    public function getBreadcrumb(): string
    {
        return 'Quản lý ảnh';
    }
}
