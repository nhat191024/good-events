<div wire:poll.90s="loadUnreadCount">
    <a class="fi-icon-btn relative flex h-9 w-9 items-center justify-center rounded-lg text-gray-400 outline-none transition duration-75 hover:bg-gray-50 hover:text-gray-500 focus-visible:bg-gray-50 focus-visible:text-gray-500 dark:text-gray-500 dark:hover:bg-white/5 dark:hover:text-gray-400 dark:focus-visible:bg-white/5 dark:focus-visible:text-gray-400"
        href="{{ route('filament.partner.pages.chat') }}" title="{{ __('chat.title') }}">
        <div class="relative">
            {{-- Chat Icon --}}
            <x-heroicon-o-chat-bubble-left-right class="h-6 w-6 stroke-2" />

            {{-- Badge --}}
            @if ($unreadCount > 0)
                <span class="absolute -right-1 -top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-primary-50 px-1 text-[10px] font-medium text-primary-600 ring-1 ring-primary-500/10 dark:bg-primary-400/10 dark:text-primary-400 dark:ring-primary-400/20">
                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                </span>
            @endif
        </div>
    </a>
</div>
