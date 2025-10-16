@if ($imageUrl)
    <div class="flex items-center justify-center">
        <img class="mx-auto max-h-[70vh] max-w-full rounded-lg object-contain" src="{{ $imageUrl }}" alt="Arrival Photo" />
    </div>
@else
    <div class="flex items-center justify-center p-8 text-gray-500 dark:text-gray-400">
        <p>{{ __('No image available') }}</p>
    </div>
@endif
