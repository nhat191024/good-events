<x-filament-panels::page>
    <form wire:submit="save">
        <div class="flex justify-end gap-x-3 mb-4">
            <x-filament::button type="submit">
                {{ __('filament-panels::resources/pages/edit-record.form.actions.save.label') }}
            </x-filament::button>
        </div>

        {{ $this->form }}
    </form>
</x-filament-panels::page>
