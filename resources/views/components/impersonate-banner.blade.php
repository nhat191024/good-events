@php
    use STS\FilamentImpersonate\Facades\Impersonation;
@endphp

@if (Impersonation::isImpersonating())
    <div class="fixed bottom-4 left-4 z-[9999] flex items-center gap-x-4 rounded-xl border border-gray-800 bg-gray-900/90 px-6 py-3 text-white shadow-2xl ring-1 ring-white/10 backdrop-blur-md transition-all duration-300 ease-out hover:scale-[1.02] hover:shadow-black/50 dark:border-gray-800 dark:bg-gray-950/90">
        <div class="flex flex-col">
            <span class="mb-0.5 text-[10px] font-bold uppercase tracking-wider text-gray-400">{{ __('Impersonating user') }}</span>
            <span class="max-w-[200px] truncate text-sm font-bold leading-tight" title="{{ auth()->user()->name }}">{{ auth()->user()->name }}</span>
        </div>

        <div class="mx-2 h-6 w-px bg-gray-700"></div>

        <a class="group relative inline-flex items-center justify-center overflow-hidden rounded-lg bg-red-600 px-4 py-2 text-xs font-bold uppercase tracking-wide text-white shadow-lg transition-all duration-200 hover:bg-red-500 hover:shadow-red-900/30 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 focus:ring-offset-gray-900"
            href="{{ route('filament-impersonate.leave') }}">
            <span class="relative z-10">{{ __('Leave impersonation') }}</span>
        </a>
    </div>
@endif
