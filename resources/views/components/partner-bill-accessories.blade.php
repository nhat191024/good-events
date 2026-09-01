@props([
    'accessories' => [],
])

@if (count($accessories) > 0)
    <div {{ $attributes->class(['rounded-lg border border-primary-200 bg-primary-50/60 p-3 dark:border-primary-800 dark:bg-primary-950/30']) }}>
        <div class="mb-2 flex items-center gap-2 text-sm font-semibold text-primary-800 dark:text-primary-300">
            <x-heroicon-o-cube class="h-4 w-4" />
            <span>Phụ kiện khách chọn</span>
        </div>
        <div class="flex flex-wrap gap-2">
            @foreach ($accessories as $accessory)
                <span class="rounded-full border border-primary-200 bg-white px-3 py-1 text-xs font-medium text-primary-800 dark:border-primary-700 dark:bg-gray-900 dark:text-primary-300">
                    {{ data_get($accessory, 'name') }}
                </span>
            @endforeach
        </div>
    </div>
@endif
