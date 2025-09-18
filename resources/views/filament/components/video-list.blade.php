<div class="space-y-4">
    @foreach ($videos as $video)
        <div class="rounded-lg border border-gray-200 p-4 dark:border-gray-700">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <h4 class="text-sm font-medium text-black dark:text-white">
                        {{ $video->name }}
                    </h4>
                    @if ($video->description)
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ $video->description }}
                        </p>
                    @endif
                    <div class="mt-2">
                        <a class="text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 inline-flex items-center text-sm" href="{{ $video->url }}" target="_blank">
                            <svg class="mr-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h1m4 0h1m-6 4h1m4 0h1m6-1a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ __('partner/service.button.watch_videos') }}
                            <svg class="ml-1 h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>
