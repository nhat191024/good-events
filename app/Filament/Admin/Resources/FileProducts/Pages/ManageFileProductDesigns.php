<?php

namespace App\Filament\Admin\Resources\FileProducts\Pages;

use App\Filament\Admin\Resources\FileProducts\FileProductResource;
use App\Models\FileProduct;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Facades\Storage;

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

        $paths = $record->files()
            ->pluck('path')
            ->map(fn($path) => str_replace('\\', '/', $path))
            ->toArray();

        $this->form->fill([
            'designs' => $paths
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

                            ->disk('s3')

                            ->multiple()
                            ->downloadable()

                            ->fetchFileInformation(false)

                            ->maxFiles(20)
                            ->maxSize(1024 * 1024 * 1000) // 1000MB
                            ->helperText('Có thể upload tối đa 20 file, mỗi file tối đa 1Gb. Hỗ trợ các định dạng ảnh (jpg, png, jpeg, webp), file nén (zip, rar), và các định dạng Adobe (psd, ai, eps, indd).')
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
                                'image/vnd.adobe.photoshop',
                                'application/vnd.adobe.illustrator',
                                'application/x-photoshop',
                                'application/photoshop',
                                'application/psd',
                                'image/psd',
                                // Vector
                                'application/postscript',     // .ai, .eps
                                'application/x-indesign',     // .indd
                            ])

                            ->saveUploadedFileUsing(function (AdvancedFileUpload $component, string $temporaryFileUploadPath, string $temporaryFileUploadFilename, ?string $originalFilename = null) {
                                $temporaryFileUploadDisk = $component->getTemporaryFileUploadDisk();
                                $disk = $component->getDisk();
                                $directory = $component->getDirectory();

                                // Use original filename to preserve extension
                                $filename = $originalFilename ?? $temporaryFileUploadFilename;

                                // Generate unique filename while preserving original extension
                                $extension = pathinfo($filename, PATHINFO_EXTENSION);
                                $basename = pathinfo($filename, PATHINFO_FILENAME);
                                $uniqueFilename = $basename . '_' . time() . '_' . uniqid() . '.' . $extension;

                                $s3Path = $directory . '/' . $uniqueFilename;
                                $s3Path = str_replace('\\', '/', $s3Path);

                                $disk->put(
                                    $s3Path,
                                    $temporaryFileUploadDisk->readStream($temporaryFileUploadPath)
                                );

                                // Return the full S3 path
                                return $s3Path;
                            })

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
        $state = $this->form->getState();
        $paths = $state['designs'] ?? [];

        $paths = array_map(fn($path) => str_replace('\\', '/', $path), $paths);

        $this->syncFiles($paths);

        Notification::make()
            ->success()
            ->title('Đã cập nhật file thành công!')
            ->send();
    }

    protected function syncFiles(array $newPaths): void
    {
        $disk = Storage::disk('s3');

        $newPaths = array_map(fn($path) => str_replace('\\', '/', $path), $newPaths);

        $existingFiles = $this->record->files()->get();
        $existingPaths = $existingFiles->pluck('path')->toArray();

        $pathsToDelete = array_diff($existingPaths, $newPaths);

        foreach ($pathsToDelete as $path) {
            $fileRecord = $existingFiles->firstWhere('path', $path);
            if ($fileRecord) {
                if ($disk->exists($path)) {
                    $disk->delete($path);
                }
                $fileRecord->delete();
            }
        }

        $pathsToAdd = array_diff($newPaths, $existingPaths);

        foreach ($pathsToAdd as $path) {
            if ($disk->exists($path)) {
                $this->record->files()->create([
                    'disk' => 's3',
                    'path' => $path,
                    'name' => basename($path),
                    'file_name' => basename($path),
                    'mime_type' => $disk->mimeType($path),
                    'size' => $disk->size($path),
                ]);
            }
        }
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
