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

                            ->temporaryFileUploadDisk('s3')
                            ->temporaryFileUploadDirectory('tmp')
                            ->disk('s3')
                            ->directory('file-product-designs/' . $this->record->id)

                            // ->downloadable()

                            ->fetchFileInformation(false)

                            ->maxSize(1024 * 1024 * 1000) // 1000MB
                            ->helperText('Có thể upload tối đa 1 file nén. Dung lượng tối đa 1Gb. Hỗ trợ các định dạng file zip.')
                            ->acceptedFileTypes([
                                // 'image/*',
                                // 'application/pdf',
                                // Compression
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
        $state = $this->form->getState();
        $paths = $state['designs'] ?? [];

        if (is_string($paths)) {
            $paths = [$paths];
        }

        $paths = array_map(fn($path) => str_replace('\\', '/', $path), $paths);

        // Handle moving temporary files
        $disk = Storage::disk('s3');
        $finalPaths = [];

        foreach ($paths as $path) {
            if (str_starts_with($path, 'tmp/')) {
                if ($disk->exists($path)) {
                    $filename = basename($path);
                    $extension = pathinfo($filename, PATHINFO_EXTENSION);
                    $basename = pathinfo($filename, PATHINFO_FILENAME);
                    $uniqueFilename = $basename . '_' . time() . '_' . uniqid() . '.' . $extension;

                    $newPath = 'file-product-designs/' . $this->record->id . '/' . $uniqueFilename;

                    $disk->move($path, $newPath);
                    $finalPaths[] = $newPath;
                }
            } else {
                $finalPaths[] = $path;
            }
        }

        $this->syncFiles($finalPaths);

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
        return 'Quản lý File - ' . $this->record->name;
    }

    public function getBreadcrumb(): string
    {
        return 'Quản lý File';
    }
}
