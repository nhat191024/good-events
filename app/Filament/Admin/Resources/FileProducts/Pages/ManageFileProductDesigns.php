<?php

namespace App\Filament\Admin\Resources\FileProducts\Pages;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;

use App\Models\FileProduct;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

use RalphJSmit\Filament\Upload\Filament\Forms\Components\AdvancedFileUpload;

class ManageFileProductDesigns extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = FileProductResource::class;

    protected string $view = 'filament.admin.resources.file-products.pages.manage-designs';

    public ?array $data = [];

    public FileProduct $record;

    public function mount(FileProduct $record): void
    {
        $this->record = $record;

        $file = $record->files()->orderByDesc('created_at')->first();
        $path = $file ? str_replace('\\', '/', $file->path) : null;

        $this->form->fill([
            'designs' => $path
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Quản lý file thiết kế')
                    ->description('Upload và quản lý các file design cho sản phẩm này')
                    ->schema([
                        AdvancedFileUpload::make('designs')
                            ->label('File Thiết Kế')
                            ->uploadingMessage('Đang tải file thiết kế lên...')

                            ->temporaryFileUploadDisk('s3')
                            ->temporaryFileUploadDirectory('tmp')
                            ->disk('s3')
                            ->directory($this->record->id)

                            ->preserveFilenames()

                            ->fetchFileInformation(false)

                            ->maxSize(1024 * 1024 * 1000) // 1000MB
                            ->helperText('Có thể upload tối đa 1 file nén. Dung lượng tối đa 1Gb. Hỗ trợ các định dạng file zip.')
                            ->acceptedFileTypes([
                                'application/zip',
                                'application/x-zip-compressed',
                                'multipart/x-zip',
                                'application/x-rar-compressed',
                                'application/vnd.rar',
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
        try {
            $state = $this->form->getState();
            $path = $state['designs'] ?? null;
            $disk = Storage::disk('s3');
            $existingFiles = $this->record->files()->get();

            if ($existingFiles->isNotEmpty()) {
                foreach ($existingFiles as $existingFile) {
                    if ($disk->exists($existingFile->path)) {
                        $disk->delete($existingFile->path);
                    }
                    $existingFile->delete();
                }
            }

            if ($path) {
                $path = $this->record->id . '/' . basename($path);
            }

            if ($path) {
                if ($disk->exists($path)) {
                    $this->record->files()->create([
                        'disk' => 's3',
                        'path' => $path,
                        'name' => basename($path),
                        'file_name' => basename($path),
                        'mime_type' => $disk->mimeType($path),
                        'size' => $disk->size($path),
                    ]);
                } else {
                    Notification::make()
                        ->danger()
                        ->title('Lỗi: File không tồn tại trên S3!')
                        ->send();

                    return;
                }
            }

            Notification::make()
                ->success()
                ->title('Đã cập nhật file thành công!')
                ->send();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Lỗi khi lưu file thiết kế! Nhà phát đã được thông báo. Xin lỗi vì bất tiện này.')
                ->send();

            Log::error('Lỗi khi lưu file thiết kế cho sản phẩm ID ' . $this->record->id . ': ' . $e->getMessage());
            return;
        }
    }

    public function getTitle(): string
    {
        return 'Quản lý File - ' . $this->record->name;
    }

    public function getBreadcrumb(): string
    {
        return 'Quản lý File';
    }
}
