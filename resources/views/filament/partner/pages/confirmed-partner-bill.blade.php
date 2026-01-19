<x-filament-panels::page x-data="{
    showMarkInJobModal: false,
    showCompleteModal: false,
    selectedBillId: null,
    selectedBillCode: '',
    openMarkInJobModal(billId, billCode) {
        this.selectedBillId = billId;
        this.selectedBillCode = billCode;
        this.showMarkInJobModal = true;
    },
    openCompleteModal(billId, billCode) {
        this.selectedBillId = billId;
        this.selectedBillCode = billCode;
        this.showCompleteModal = true;
    },
    closeModals() {
        this.showMarkInJobModal = false;
        this.showCompleteModal = false;
        this.selectedBillId = null;
        this.selectedBillCode = '';
    }
}">
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <div class="rounded-xl border-b border-gray-200 bg-gray-50 px-4 py-4 sm:px-6 dark:border-gray-900 dark:bg-gray-800/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 sm:text-lg dark:text-white">
                        {{ __('partner/bill.bill_confirmed') }} ({{ $this->bills->total() }})
                    </h3>
                </div>

                {{-- Filters Section - Stack on mobile --}}
                <div class="mt-4 space-y-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 lg:grid-cols-4">
                    {{-- Search --}}
                    <div class="relative sm:col-span-2 lg:col-span-1">
                        <input class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white dark:placeholder-gray-400" type="text" placeholder="{{ __('partner/bill.search_placeholder') }}"
                            wire:model.live.debounce.300ms="searchQuery" />
                        <div class="absolute right-2 top-2" wire:loading wire:target="searchQuery">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Date Filter --}}
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" wire:model.live="dateFilter">
                            <option value="all">{{ __('partner/bill.filter_date') }}</option>
                            <option value="today">{{ __('partner/bill.today') }}</option>
                            <option value="tomorrow">{{ __('partner/bill.tomorrow') }}</option>
                            <option value="this_week">{{ __('partner/bill.this_week') }}</option>
                            <option value="next_week">{{ __('partner/bill.next_week') }}</option>
                            <option value="this_month">{{ __('partner/bill.this_month') }}</option>
                        </select>
                        <div class="absolute right-8 top-2" wire:loading wire:target="dateFilter">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Sort By --}}
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" wire:model.live="sortBy">
                            <option value="newest">{{ __('partner/bill.newest_first') }}</option>
                            <option value="oldest">{{ __('partner/bill.oldest_first') }}</option>
                            <option value="date_asc">{{ __('partner/bill.event_date_asc') }}</option>
                            <option value="date_desc">{{ __('partner/bill.event_date_desc') }}</option>
                        </select>
                        <div class="absolute right-8 top-2" wire:loading wire:target="sortBy">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Clear Filters --}}
                    <div class="flex items-end">
                        <button class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            wire:click="clearFilters" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="clearFilters">{{ __('partner/bill.clear_filters') }}</span>
                            <span class="flex items-center justify-center" wire:loading wire:target="clearFilters">
                                <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('partner/bill.clear_filters') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bills Grid --}}
        @if ($this->bills->count() > 0)
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ($this->bills as $bill)
                    <div class="hover:border-primary-500 dark:hover:border-primary-500 group flex cursor-pointer flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-200 hover:shadow-lg dark:border-gray-800 dark:bg-gray-800" wire:click="viewBill({{ $bill->id }})">
                        {{-- Status Badge --}}
                        <div class="flex-1 p-6 pb-4">
                            <div class="mb-4 flex items-start justify-between">
                                <div class="flex-1">
                                    <h3 class="group-hover:text-primary-600 dark:group-hover:text-primary-400 text-lg font-semibold text-gray-900 transition-colors dark:text-white">
                                        {{ $bill->code }}
                                    </h3>
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ $bill->category?->name }}
                                    </p>
                                </div>
                                @php
                                    $statusClasses = match ($bill->status->value) {
                                        'pending' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
                                        'confirmed' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
                                        'in_job' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-900/30 dark:text-gray-400',
                                    };
                                @endphp
                                <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-3 py-1 text-base font-semibold">
                                    {{ $this->getStatusLabel($bill->status->value) }}
                                </span>
                            </div>

                            {{-- Client Info --}}
                            <div class="mb-4 flex items-center gap-3">
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $bill->client?->name ?? __('partner/bill.unknown_client') }}
                                    </p>
                                </div>
                            </div>

                            {{-- Event Details --}}
                            <div class="mb-4 space-y-2">
                                <div class="flex justify-start gap-10">
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <x-filament::icon class="h-4 w-4 text-gray-400" icon="heroicon-o-calendar" />
                                        <span>{{ $bill->date?->format('d/m/Y') ?? __('partner/bill.no_date') }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
                                        <x-filament::icon class="h-4 w-4 text-gray-400" icon="heroicon-o-clock" />
                                        <span>
                                            {{ $bill->start_time?->format('H:i') }} - {{ $bill->end_time?->format('H:i') }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-filament::icon class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" icon="heroicon-o-map-pin" />
                                    <span class="line-clamp-2">{{ $bill->address }}</span>
                                </div>
                            </div>

                            {{-- Partner Detail Info --}}
                            <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                @php
                                    $partnerDetail = $bill->details->first();
                                @endphp
                                @if ($partnerDetail->status == 'new')
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('partner/bill.your_price') }}
                                        </span>
                                        <span class="text-primary-600 dark:text-primary-400 text-lg font-bold">
                                            {{ number_format($partnerDetail->total ?? 0, 0, ',', '.') }} ₫
                                        </span>
                                    </div>
                                @else
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('partner/bill.final_total') }}
                                        </span>
                                        <span class="text-primary-600 dark:text-primary-400 text-lg font-bold">
                                            {{ number_format($bill->final_total ?? 0, 0, ',', '.') }} ₫
                                        </span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Footer --}}
                        <div class="mt-auto flex h-full flex-col justify-end border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900/50">
                            <div class="mb-3 flex gap-1">
                                {{-- Button "Đã đến nơi" for CONFIRMED status --}}
                                @if ($bill->status->value === 'confirmed')
                                    <button class="bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400 w-full rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                        type="button" @click.stop="openMarkInJobModal({{ $bill->id }}, '{{ $bill->code }}')">
                                        <x-filament::icon class="mr-1 inline-block h-4 w-4" icon="heroicon-o-map-pin" />
                                        {{ __('partner/bill.mark_as_arrived') }}
                                    </button>
                                    @if ($bill->thread_id)
                                        <a class="flex w-full flex-1 items-center justify-center rounded-lg bg-blue-600 p-4 text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                            href="{{ route('filament.partner.pages.chat') }}?chat={{ $bill->thread_id }}" @click.stop>
                                            <x-filament::icon class="size-8" icon="heroicon-o-chat-bubble-oval-left-ellipsis" />
                                        </a>
                                    @endif
                                @endif

                                {{-- Button "Hoàn thành" for IN_JOB status --}}
                                @if ($bill->status->value === 'in_job')
                                    <button class="bg-success-600 hover:bg-success-700 focus:ring-success-500 dark:bg-success-500 dark:hover:bg-success-400 w-full rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                        type="button" @click.stop="openCompleteModal({{ $bill->id }}, '{{ $bill->code }}')">
                                        <x-filament::icon class="mr-1 inline-block h-4 w-4" icon="heroicon-o-check-circle" />
                                        {{ __('partner/bill.complete_order') }}
                                    </button>
                                    @if ($bill->thread_id)
                                        <a class="flex w-full flex-1 items-center justify-center rounded-lg bg-blue-600 p-4 text-white shadow-sm transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-gray-900"
                                            href="{{ route('filament.partner.pages.chat') }}?chat={{ $bill->thread_id }}" @click.stop>
                                            <x-filament::icon class="size-8" icon="heroicon-o-chat-bubble-oval-left-ellipsis" />
                                        </a>
                                    @endif
                                @endif
                            </div>

                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ __('partner/bill.updated') }}: {{ $bill->updated_at->diffForHumans() }}</span>
                                <x-filament::icon class="text-primary-500 h-4 w-4 transition-transform group-hover:translate-x-1" icon="heroicon-o-arrow-right" />
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $this->bills->links('pagination::tailwind') }}
            </div>
        @else
            {{-- Empty State --}}
            <div class="rounded-xl border border-gray-200 bg-white p-12 shadow-sm dark:border-gray-700 dark:bg-gray-800">
                <div class="text-center">
                    <x-filament::icon class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-gray-600" icon="heroicon-o-calendar-date-range" />
                    <h3 class="mb-2 text-lg font-medium text-gray-900 dark:text-white">
                        {{ __('partner/bill.no_bills_found') }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('partner/bill.no_bills_filter_description') }}
                    </p>
                </div>
            </div>
        @endif
    </div>

    {{-- Modals --}}
    <x-partner.mark-in-job-modal />
    <x-partner.complete-order-modal />
</x-filament-panels::page>
