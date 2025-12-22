<?php

namespace App\Filament\Admin\Resources\Partners\Pages;

use App\Filament\Admin\Resources\Partners\PartnerResource;

use App\Enum\PartnerServiceStatus;

use App\Models\PartnerCategory;
use App\Models\PartnerService;
use App\Models\PartnerMedia;

use Filament\Resources\Pages\Page;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;

use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;

use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;

use Filament\Notifications\Notification;

use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Support\Htmlable;

class ManagePartnerServices extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = PartnerResource::class;

    protected string $view = 'filament.admin.resources.partners.pages.manage-partner-services';

    public $record;

    public ?array $data = [];

    public function getTitle(): string | Htmlable
    {
        return __('Quản lý dịch vụ cho đối tác :name', ['name' => $this->record->partnerProfile->partner_name]);
    }

    public function mount($record): void
    {
        $this->record = $this->resolveRecord($record);

        $existingServices = PartnerService::getByUserCached($this->record->id)
            ->keyBy('category_id');

        $formData = [];

        $categories = PartnerCategory::getAllCached();

        foreach ($categories as $category) {
            if ($existingServices->get($category->id)) {
                $formData['services'][$category->id] = [
                    'enabled' => true,
                ];
            } else {
                $formData['services'][$category->id] = [
                    'enabled' => false,
                ];
            }
        }

        $this->form->fill($formData);
    }

    public function form(Schema $schema): Schema
    {
        $categoriesTree = PartnerCategory::getTree();

        $sections = [];

        foreach ($categoriesTree as $parentCategory) {
            $fields = [];

            foreach ($parentCategory->children as $childCategory) {
                $fields[] = Checkbox::make("services.{$childCategory->id}.enabled")
                    ->label($childCategory->name)
                    ->reactive();
            }

            if (!empty($fields)) {
                $sections[] = Section::make($parentCategory->name)
                    ->schema([
                        Grid::make(3)
                            ->schema($fields)
                    ])
                    ->collapsible();
            }
        }

        return $schema
            ->components($sections)
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        DB::transaction(function () use ($data) {
            foreach ($data['services'] as $categoryId => $serviceData) {
                if ($serviceData['enabled']) {
                    if (! PartnerCategory::where('id', $categoryId)->exists()) {
                        continue;
                    }

                    $service = PartnerService::updateOrCreate(
                        [
                            'user_id' => $this->record->id,
                            'category_id' => $categoryId,
                        ],
                        [
                            'status' => PartnerServiceStatus::APPROVED,
                        ]
                    );

                    // Handle Media
                    PartnerMedia::updateOrCreate(
                        ['partner_service_id' => $service->id],
                        [
                            'name' => 'Service Info',
                            'url' => 'https://youtube.com',
                            'description' => '',
                        ]
                    );
                } else {
                    // Delete if exists
                    $service = PartnerService::where('user_id', $this->record->id)
                        ->where('category_id', $categoryId)
                        ->first();

                    if ($service) {
                        $service->delete();
                    }
                }
            }
        });

        Notification::make()
            ->success()
            ->title(__('global.system_message.saved_success'))
            ->send();
    }

    protected function resolveRecord($key)
    {
        return $this->getResource()::getEloquentQuery()->find($key);
    }
}
