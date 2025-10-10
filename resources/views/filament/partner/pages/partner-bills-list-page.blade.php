<x-filament-panels::page x-data="{
    showMarkInJobModal: false,
    showCompleteModal: false,
    selectedBillId: null,
    selectedBillCode: '',
    openMarkInJobModal(billId, billCode) {
        console.log('openMarkInJobModal called', billId, billCode);
        this.selectedBillId = billId;
        this.selectedBillCode = billCode;
        this.showMarkInJobModal = true;
    },
    openCompleteModal(billId, billCode) {
        console.log('openCompleteModal called', billId, billCode);
        this.selectedBillId = billId;
        this.selectedBillCode = billCode;
        this.showCompleteModal = true;
    },
    closeModals() {
        console.log('closeModals called');
        this.showMarkInJobModal = false;
        this.showCompleteModal = false;
        this.selectedBillId = null;
        this.selectedBillCode = '';
    }
}">
    <div class="space-y-6">
        {{-- Header Section --}}
        <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 sm:px-6 dark:border-gray-700 dark:bg-gray-700/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 sm:text-lg dark:text-white">
                        {{ __('partner/bill.bill') }} ({{ count($bills) }})
                    </h3>
                </div>

                {{-- Filters Section - Stack on mobile --}}
                <div class="mt-4 space-y-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 lg:grid-cols-5">
                    {{-- Search --}}
                    <div class="relative sm:col-span-2 lg:col-span-1">
                        <input class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" type="text" placeholder="{{ __('partner/bill.search_placeholder') }}"
                            wire:model.live.debounce.300ms="searchQuery" />
                        <div class="absolute right-2 top-2" wire:loading wire:target="searchQuery">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Status Filter --}}
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" wire:model.live="statusFilter">
                            <option value="all">{{ __('partner/bill.all_status') }}</option>
                            <option value="pending">{{ __('partner/bill.pending') }}</option>
                            <option value="confirmed">{{ __('partner/bill.status_confirmed') }}</option>
                        </select>
                        <div class="absolute right-8 top-2" wire:loading wire:target="statusFilter">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Date Filter --}}
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" wire:model.live="dateFilter">
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
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    {{-- Sort By --}}
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" wire:model.live="sortBy">
                            <option value="newest">{{ __('partner/bill.newest_first') }}</option>
                            <option value="oldest">{{ __('partner/bill.oldest_first') }}</option>
                            <option value="date_asc">{{ __('partner/bill.event_date_asc') }}</option>
                            <option value="date_desc">{{ __('partner/bill.event_date_desc') }}</option>
                        </select>
                        <div class="absolute right-8 top-2" wire:loading wire:target="sortBy">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
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
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ __('partner/bill.clear_filters') }}
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bills Grid --}}
        @if (count($bills) > 0)
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3">
                @foreach ($bills as $bill)
                    <div class="hover:border-primary-500 dark:hover:border-primary-500 group cursor-pointer overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all duration-200 hover:shadow-lg dark:border-gray-700 dark:bg-gray-800" wire:click="viewBill({{ $bill->id }})">
                        {{-- Status Badge --}}
                        <div class="p-6 pb-4">
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
                                <span class="{{ $statusClasses }} inline-flex items-center rounded-full px-3 py-1 text-xs font-medium">
                                    {{ $this->getStatusLabel($bill->status->value) }}
                                </span>
                            </div>

                            {{-- Client Info --}}
                            <div class="mb-4 flex items-center gap-3">
                                <div class="from-primary-400 to-primary-600 flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br font-semibold text-white">
                                    {{ substr($bill->client?->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p class="truncate text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $bill->client?->name ?? __('partner/bill.unknown_client') }}
                                    </p>
                                    <p class="truncate text-xs text-gray-500 dark:text-gray-400">
                                        {{ $bill->phone }}
                                    </p>
                                </div>
                            </div>

                            {{-- Event Details --}}
                            <div class="mb-4 space-y-2">
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
                                <div class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                                    <x-filament::icon class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" icon="heroicon-o-map-pin" />
                                    <span class="line-clamp-2">{{ $bill->address }}</span>
                                </div>
                            </div>

                            {{-- Partner Detail Info --}}
                            @php
                                $partnerDetail = $bill->details->first();
                            @endphp
                            @if ($partnerDetail)
                                <div class="border-t border-gray-200 pt-4 dark:border-gray-700">
                                    <div class="flex items-center justify-between">
                                        <span class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('partner/bill.your_price') }}
                                        </span>
                                        <span class="text-primary-600 dark:text-primary-400 text-lg font-bold">
                                            {{ number_format($partnerDetail->total ?? 0, 0, ',', '.') }} ₫
                                        </span>
                                    </div>
                                    @if ($partnerDetail->status)
                                        <div class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                            {{ __('partner/bill.detail_status') }}:
                                            <span class="font-medium">{{ $partnerDetail->status }}</span>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Footer --}}
                        <div class="border-t border-gray-200 bg-gray-50 px-6 py-3 dark:border-gray-700 dark:bg-gray-900/50">
                            {{-- Button "Đã đến nơi" for CONFIRMED status --}}
                            @if ($bill->status->value === 'confirmed')
                                <div class="mb-3">
                                    <button class="bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 w-full rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2" type="button"
                                        @click.stop="openMarkInJobModal({{ $bill->id }}, '{{ $bill->code }}')">
                                        <x-filament::icon class="mr-1 inline-block h-4 w-4" icon="heroicon-o-map-pin" />
                                        {{ __('partner/bill.mark_as_arrived') }}
                                    </button>
                                </div>
                            @endif

                            {{-- Button "Hoàn thành" for IN_JOB status --}}
                            @if ($bill->status->value === 'in_job')
                                <div class="mb-3">
                                    <button class="bg-success-600 hover:bg-success-700 focus:ring-success-500 w-full rounded-lg px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors focus:outline-none focus:ring-2 focus:ring-offset-2" type="button"
                                        @click.stop="openCompleteModal({{ $bill->id }}, '{{ $bill->code }}')">
                                        <x-filament::icon class="mr-1 inline-block h-4 w-4" icon="heroicon-o-check-circle" />
                                        {{ __('partner/bill.complete_order') }}
                                    </button>
                                </div>
                            @endif

                            <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                                <span>{{ __('partner/bill.updated') }}: {{ $bill->updated_at->diffForHumans() }}</span>
                                <x-filament::icon class="text-primary-500 h-4 w-4 transition-transform group-hover:translate-x-1" icon="heroicon-o-arrow-right" />
                            </div>
                        </div>
                    </div>
                @endforeach
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

    {{-- Auto-refresh every 30 seconds --}}
    <div wire:poll.30s="loadBills"></div>

    {{-- Modal: Mark as In Job --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-show="showMarkInJobModal" style="display: none;" @click.self="closeModals()" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('partner/bill.mark_as_arrived') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('partner/bill.mark_in_job_confirm') }}
                    </p>
                </div>
                <button class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300" @click="closeModals()">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="mb-6">
                <div class="rounded-lg bg-blue-50 p-4 dark:bg-blue-900/20">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-blue-800 dark:text-blue-300">
                                {{ __('partner/bill.bill_code') }}: <span x-text="selectedBillCode"></span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" type="button"
                    @click="closeModals()">
                    {{ __('partner/bill.cancel') }}
                </button>
                <button class="bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 flex-1 rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2" type="button" @click="$wire.markAsInJob(selectedBillId).then(() => closeModals())" wire:loading.attr="disabled"
                    wire:target="markAsInJob">
                    <span wire:loading.remove wire:target="markAsInJob">
                        {{ __('global.confirm') }}
                    </span>
                    <span class="flex items-center justify-center" wire:loading wire:target="markAsInJob">
                        <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('global.processing') }}...
                    </span>
                </button>
            </div>
        </div>
    </div>

    {{-- Modal: Complete Order --}}
    <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-show="showCompleteModal" style="display: none;" @click.self="closeModals()" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
        <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-2xl dark:bg-gray-800" @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
            {{-- Header --}}
            <div class="mb-4 flex items-start justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        {{ __('partner/bill.complete_order') }}
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('partner/bill.complete_order_confirm_description') }}
                    </p>
                </div>
                <button class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300" @click="closeModals()">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Content --}}
            <div class="mb-6">
                <div class="rounded-lg bg-green-50 p-4 dark:bg-green-900/20">
                    <div class="flex items-center gap-3">
                        <div class="flex-shrink-0">
                            <svg class="h-5 w-5 text-green-600 dark:text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                {{ __('partner/bill.bill_code') }}: <span x-text="selectedBillCode"></span>
                            </p>
                            <p class="mt-1 text-xs text-green-600 dark:text-green-400">
                                {{ __('partner/bill.fee_will_be_deducted') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex gap-3">
                <button class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" type="button"
                    @click="closeModals()">
                    {{ __('partner/bill.cancel') }}
                </button>
                <button class="bg-success-600 hover:bg-success-700 focus:ring-success-500 flex-1 rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2" type="button" @click="$wire.completeBill(selectedBillId).then(() => closeModals())" wire:loading.attr="disabled"
                    wire:target="completeBill">
                    <span wire:loading.remove wire:target="completeBill">
                        {{ __('partner/bill.confirm_complete') }}
                    </span>
                    <span class="flex items-center justify-center" wire:loading wire:target="completeBill">
                        <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        {{ __('global.processing') }}...
                    </span>
                </button>
            </div>
        </div>
    </div>
</x-filament-panels::page>
