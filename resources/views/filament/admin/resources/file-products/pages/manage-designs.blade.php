<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        <div class="mt-6 flex justify-end gap-3">
            <x-filament::button type="button" color="gray" tag="a" :href="$this->getResource()::getUrl('index')">
                Hủy
            </x-filament::button>

            <x-filament::button type="submit" form="save">
                Lưu thay đổi
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
