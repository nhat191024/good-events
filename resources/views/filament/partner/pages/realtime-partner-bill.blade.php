<x-filament-panels::page>
    <div class="space-y-6" wire:poll.30s="autoRefresh">
        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <x-heroicon-s-check-circle class="h-5 w-5 text-green-400" />
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ session('success') }}
                            </p>
                        </div>
                    </div>
                    {{-- go to my show button --}}
                    <a class="ml-4 inline-flex items-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700 focus:border-blue-900 focus:outline-none focus:ring focus:ring-blue-300 active:bg-blue-900 disabled:opacity-25"
                        href="{{ route('filament.partner.pages.view-partner-bill', ['record' => session('show_id')]) }}">
                        {{ __('partner/bill.go_to_show') }}
                    </a>
                </div>
            </div>
        @endif

        @if (session()->has('error'))
            <div class="rounded-md bg-red-50 p-4 dark:bg-red-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-x-circle class="h-5 w-5 text-red-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">
                            {{ session('error') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        @if (session()->has('info'))
            <div class="rounded-md bg-blue-50 p-4 dark:bg-blue-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-information-circle class="h-5 w-5 text-blue-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                            {{ session('info') }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <!-- Header với refresh button - responsive -->
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 sm:text-xl dark:text-white">
                    {{ __('partner/bill.new_bill_description') }}
                </h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('partner/bill.last_updated') }}: {{ $lastUpdated ?? 'Never' }}
                </p>
            </div>
            <button
                class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-blue-600 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-blue-700 focus:border-blue-900 focus:outline-none focus:ring focus:ring-blue-300 active:bg-blue-900 disabled:opacity-25 sm:w-auto"
                type="button" wire:click="refreshBills">
                <x-heroicon-o-arrow-path class="mr-2 h-4 w-4" />
                {{ __('partner/bill.refresh') }}
            </button>
        </div>

        <!-- Partner Bills List -->
        <div class="rounded-lg bg-white shadow-sm dark:bg-gray-800">
            <!-- Header với filters -->
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 sm:px-6 dark:border-gray-700 dark:bg-gray-800/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 sm:text-lg dark:text-white">
                        {{ __('partner/bill.pending_orders') }} ({{ count($partnerBills) }})
                    </h3>
                </div>

                <!-- Filters Section - Stack on mobile -->
                <div class="mt-4 space-y-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 lg:grid-cols-4">
                    <!-- Search -->
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

                    <!-- Date Filter -->
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" wire:model.live="dateFilter">
                            <option value="all">{{ __('partner/bill.filter_date') }}</option>
                            <option value="today">{{ __('partner/bill.today') }}</option>
                            <option value="this_week">{{ __('partner/bill.this_week') }}</option>
                            <option value="this_month">{{ __('partner/bill.this_month') }}</option>
                        </select>
                        <div class="absolute right-8 top-2" wire:loading wire:target="dateFilter">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Category Filter -->
                    <div class="relative">
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-white" wire:model.live="categoryFilter">
                            <option value="all">{{ __('partner/bill.all_categories') }}</option>
                            @foreach ($availableCategories as $category)
                                <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                            @endforeach
                        </select>
                        <div class="absolute right-8 top-2" wire:loading wire:target="categoryFilter">
                            <svg class="h-4 w-4 animate-spin text-gray-400" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Clear Filters -->
                    <div class="flex items-end">
                        <button class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700"
                            wire:click="clearFilters" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="clearFilters">{{ __('partner/bill.clear_filters') }}</span>
                            <span class="flex items-center justify-center" wire:loading wire:target="clearFilters">
                                <svg class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Đang xóa...
                            </span>
                        </button>
                    </div>
                </div>
            </div>

            @if (empty($partnerBills))
                <div class="px-4 py-12 text-center sm:px-6">
                    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-gray-100 dark:bg-gray-700">
                        <x-heroicon-o-document-text class="h-8 w-8 text-gray-400" />
                    </div>
                    <h3 class="mt-4 text-base font-medium text-gray-900 sm:text-lg dark:text-white">{{ __('partner/bill.no_bills_found') }}</h3>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('partner/bill.no_bills_description') }}
                    </p>
                </div>
            @else
                <div class="divide-y divide-gray-200 dark:divide-gray-700">
                    @foreach ($partnerBills as $bill)
                        <div class="group px-4 py-4 transition-all duration-200 hover:bg-gray-50 sm:px-6 sm:py-5 dark:hover:bg-gray-700/50">
                            <!-- Mobile Layout -->
                            <div class="block lg:hidden">
                                <!-- Header -->
                                <div class="mb-3 flex items-start justify-between">
                                    <h4 class="text-sm font-semibold text-gray-900 dark:text-white">
                                        {{ __('partner/bill.bill_code') }} #{{ $bill['code'] }}
                                    </h4>
                                    <div class="text-right">
                                        <p class="text-lg font-bold text-gray-900 dark:text-white">
                                            {{ number_format($bill['final_total'], 0) }} VNĐ
                                        </p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">
                                            {{ \Carbon\Carbon::parse($bill['created_at'])->diffForHumans() }}
                                        </p>
                                    </div>
                                </div>

                                <!-- Details in mobile card layout -->
                                <div class="space-y-2 text-sm">
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-user class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.client') }}:</span>
                                            <button class="ml-1 text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300" type="button" wire:click="openClientModal({{ $bill['client']['id'] ?? 0 }})">
                                                {{ $bill['client']['name'] ?? 'N/A' }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-tag class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.need_to_find') }}:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['category']['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-calendar-days class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.event') }}:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['event']['name'] ?? $bill['custom_event'] }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-clock class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.date') }}:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['date'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- Time Details for mobile -->
                                    @if ($bill['start_time'] || $bill['end_time'])
                                        <div class="flex flex-wrap gap-4">
                                            @if ($bill['start_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-play class="h-4 w-4 text-green-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.start_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ $bill['start_time'] ?? 'N/A' }}</span>
                                                </div>
                                            @endif
                                            @if ($bill['end_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-stop class="h-4 w-4 text-red-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.end_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ $bill['end_time'] ?? 'N/A' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Address for mobile -->
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-map-pin class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <span class="break-words">
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.address') }}:</span>
                                            <span class="mt-1 text-gray-900 dark:text-white">{{ $bill['address'] }}</span>
                                        </span>
                                    </div>

                                    <!-- Note for mobile -->
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-document-text class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <span class="break-words">
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.note') }}:</span>
                                            <span class="flex-1 text-gray-900 dark:text-white">
                                                {{ $bill['note'] }}
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Action button for mobile -->
                                <div class="mt-4">
                                    <button class="inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                        wire:click="openAcceptModal({{ $bill['id'] }})">
                                        <x-heroicon-m-check class="mr-2 h-4 w-4" />
                                        {{ __('partner/bill.accept_order') }}
                                    </button>
                                </div>
                            </div>

                            <!-- Desktop Layout (hidden on mobile) -->
                            <div class="hidden lg:flex lg:items-start lg:justify-between">
                                <!-- Bill Info -->
                                <div class="flex-1">
                                    <!-- Header with code -->
                                    <div class="mb-3 flex items-center gap-3">
                                        <h4 class="text-base font-semibold text-gray-900 dark:text-white">
                                            {{ __('partner/bill.bill_code') }} #{{ $bill['code'] }}
                                        </h4>
                                    </div>

                                    <!-- Details Grid for desktop -->
                                    <div class="grid grid-cols-1 gap-3 text-sm xl:grid-cols-5">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-user class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.client') }}:</span>
                                            <button class="text-blue-600 hover:text-blue-800 hover:underline dark:text-blue-400 dark:hover:text-blue-300" type="button" wire:click="openClientModal({{ $bill['client']['id'] ?? 0 }})">
                                                {{ $bill['client']['name'] ?? 'N/A' }}
                                            </button>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-tag class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.need_to_find') }}:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $bill['category']['name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-calendar-days class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.event') }}:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $bill['event']['name'] ?? $bill['custom_event'] }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-clock class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.date') }}:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $bill['date'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- Time Details Row for desktop -->
                                    @if ($bill['start_time'] || $bill['end_time'])
                                        <div class="mt-2 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                            @if ($bill['start_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-play class="h-4 w-4 text-green-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.start_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ $bill['start_time'] ?? 'N/A' }}</span>
                                                </div>
                                            @endif
                                            @if ($bill['end_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-stop class="h-4 w-4 text-red-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.end_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ $bill['end_time'] ?? 'N/A' }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Address & note for desktop -->
                                    <div class="mt-2 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                        <div class="mt-3 flex items-start gap-2">
                                            <x-heroicon-m-map-pin class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                            <div class="flex">
                                                <span class="pe-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.address') }}:</span>
                                                <p class="text-sm text-gray-900 dark:text-white">{{ $bill['address'] }}</p>
                                            </div>
                                        </div>
                                        <div class="mt-3 flex items-start gap-2">
                                            <x-heroicon-m-document-text class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                            <div class="flex">
                                                <span class="pe-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.note') }}:</span>
                                                <p class="text-sm text-gray-900 dark:text-white">{{ Str::limit($bill['note'], 50) }}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right side - Amount and Actions for desktop -->
                                <div class="ml-6 flex flex-col items-end gap-3">
                                    <!-- Amount -->
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 dark:text-gray-400">{{ __('partner/bill.total_amount') }}</p>
                                        <p class="text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ number_format($bill['final_total'], 0) }} VNĐ
                                        </p>
                                    </div>

                                    <!-- Time -->
                                    <p class="text-xs text-gray-500 dark:text-gray-400">
                                        {{ \Carbon\Carbon::parse($bill['created_at'])->diffForHumans() }}
                                    </p>

                                    <!-- Actions for desktop -->
                                    <div class="flex gap-2">
                                        <button class="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" wire:click="openAcceptModal({{ $bill['id'] }})">
                                            <x-heroicon-m-check class="mr-1 h-3 w-3" />
                                            {{ __('partner/bill.accept_order') }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Accept Order Modal -->
    @if ($showAcceptModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data="{ show: false }" x-init="$nextTick(() => show = true)" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800" x-show="show" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="$wire.closeAcceptModal()">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10 dark:bg-green-900/20">
                                <x-heroicon-m-currency-dollar class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 id="modal-title" class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                                    {{ __('partner/bill.enter_price') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('partner/bill.enter_price_for_order') }} <strong>#{{ $selectedBillCode }}</strong>
                                    </p>
                                </div>

                                <!-- Price Input -->
                                <div class="mt-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300" for="price">
                                        {{ __('partner/bill.price_label') }} <span class="text-red-500">*</span>
                                    </label>
                                    <div class="relative mt-2">
                                        <input id="price"
                                            class="@error('priceInput') border-red-300 bg-red-50 text-red-900 focus:border-red-500 focus:ring-red-500 dark:border-red-600 dark:bg-red-900/20 dark:text-red-400 dark:placeholder:text-red-400/60
                                            @else border-gray-300 bg-white focus:border-green-500 focus:ring-green-500 dark:border-gray-600 dark:bg-gray-900 dark:text-white dark:focus:border-green-500 dark:focus:ring-green-500 @enderror block w-full rounded-lg border py-3 pl-10 pr-16 text-base transition-colors duration-200 placeholder:text-gray-400 focus:outline-none focus:ring-2"
                                            type="number" wire:model.live="priceInput" placeholder="Nhập số tiền..." min="0" step="1000">
                                    </div>
                                    @error('priceInput')
                                        <div class="mt-2 flex items-start gap-1.5">
                                            <x-heroicon-m-exclamation-circle class="mt-0.5 h-4 w-4 flex-shrink-0 text-red-500" />
                                            <p class="text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                                        </div>
                                    @enderror
                                    @if ($priceInput && !$errors->has('priceInput'))
                                        <div class="mt-3 rounded-md bg-green-50 px-3 py-2 dark:bg-green-900/20">
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-m-check-circle class="h-5 w-5 text-green-500" />
                                                <p class="text-sm font-medium text-green-800 dark:text-green-300">
                                                    {{ __('partner/bill.formatted_price') }}: <span class="font-bold">{{ number_format($priceInput, 0, ',', '.') }} VNĐ</span>
                                                </p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-900/20">
                        <button class="inline-flex w-full justify-center rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 sm:ml-3 sm:w-auto" type="button" wire:click="acceptOrder">
                            {{ __('partner/bill.confirm_accept') }}
                        </button>
                        <button class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto dark:bg-gray-900 dark:text-white dark:ring-gray-800 dark:hover:bg-gray-600" type="button"
                            wire:click="closeAcceptModal">
                            {{ __('partner/bill.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Client Detail Modal -->
    @if ($showClientModal && $selectedClient)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="client-modal-title" role="dialog" aria-modal="true" x-data="{ show: false }" x-init="$nextTick(() => show = true)" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">
                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl sm:align-middle dark:bg-gray-800" x-show="show" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="$wire.closeClientModal()">
                    <!-- Header -->
                    <div class="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-900/50">
                        <div class="flex items-center justify-between">
                            <h3 id="client-modal-title" class="text-lg font-semibold text-gray-900 dark:text-white">
                                <x-heroicon-m-user class="mr-2 inline-block h-5 w-5 text-blue-500" />
                                Thông tin khách hàng
                            </h3>
                            <button class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 dark:hover:text-gray-300" type="button" wire:click="closeClientModal">
                                <x-heroicon-m-x-mark class="h-6 w-6" />
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6">
                        <div class="space-y-6">
                            <!-- Avatar and Name -->
                            <div class="flex items-center gap-4">
                                @if (!empty($selectedClient['avatar']))
                                    <img class="h-20 w-20 rounded-full object-cover ring-4 ring-gray-200 dark:ring-gray-700" src="{{ $selectedClient['avatar_url'] }}" alt="{{ $selectedClient['name'] }}">
                                @else
                                    <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-100 ring-4 ring-gray-200 dark:bg-blue-900/20 dark:ring-gray-700">
                                        <x-heroicon-m-user class="h-10 w-10 text-blue-600 dark:text-blue-400" />
                                    </div>
                                @endif
                                <div>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">{{ $selectedClient['name'] }}</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Khách hàng</p>
                                </div>
                            </div>

                            <!-- Statistics -->
                            @php
                                $statistics = collect($selectedClient['statistics'] ?? []);
                                $totalSpent = $statistics->firstWhere('metrics_name', 'total_spent')['metrics_value'] ?? 0;
                                $ordersPlaced = $statistics->firstWhere('metrics_name', 'orders_placed')['metrics_value'] ?? 0;
                                $completedOrders = $statistics->firstWhere('metrics_name', 'completed_orders')['metrics_value'] ?? 0;
                                $cancelledPercentage = $statistics->firstWhere('metrics_name', 'cancelled_orders_percentage')['metrics_value'] ?? 0;
                            @endphp

                            <div>
                                <h5 class="mb-3 flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                                    <x-heroicon-m-chart-bar class="h-5 w-5 text-blue-500" />
                                    Thống kê hoạt động
                                </h5>
                                <div class="grid grid-cols-2 gap-4">
                                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-shopping-bag class="h-5 w-5 text-blue-500" />
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng đơn hàng</p>
                                        </div>
                                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $ordersPlaced }}
                                        </p>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-check-circle class="h-5 w-5 text-green-500" />
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Đã hoàn thành</p>
                                        </div>
                                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $completedOrders }}
                                        </p>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-currency-dollar class="h-5 w-5 text-yellow-500" />
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tổng chi tiêu</p>
                                        </div>
                                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ number_format($totalSpent, 0, ',', '.') }} ₫
                                        </p>
                                    </div>
                                    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-x-circle class="h-5 w-5 text-red-500" />
                                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">Tỷ lệ hủy</p>
                                        </div>
                                        <p class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                                            {{ $cancelledPercentage }}%
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Info -->
                            <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-800">
                                <h5 class="mb-3 flex items-center gap-2 font-semibold text-gray-900 dark:text-white">
                                    <x-heroicon-m-user-circle class="h-5 w-5 text-blue-500" />
                                    Thông tin tài khoản
                                </h5>
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500 dark:text-gray-400">Ngày tham gia:</span>
                                        <span class="font-medium text-gray-900 dark:text-white">
                                            {{ \Carbon\Carbon::parse($selectedClient['created_at'])->format('d/m/Y') }}
                                        </span>
                                    </div>
                                    @if (!empty($selectedClient['email_verified_at']))
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500 dark:text-gray-400">Trạng thái email:</span>
                                            <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                <x-heroicon-m-check-circle class="mr-1 h-3 w-3" />
                                                Đã xác thực
                                            </span>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-between">
                                            <span class="text-gray-500 dark:text-gray-400">Trạng thái email:</span>
                                            <span class="inline-flex items-center rounded-full bg-yellow-100 px-2.5 py-0.5 text-xs font-medium text-yellow-800 dark:bg-yellow-900/20 dark:text-yellow-400">
                                                <x-heroicon-m-exclamation-circle class="mr-1 h-3 w-3" />
                                                Chưa xác thực
                                            </span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="bg-gray-50 px-6 py-4 dark:bg-gray-900/20">
                        <button class="w-full rounded-md bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 sm:w-auto" type="button" wire:click="closeClientModal">
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Banned notification Modal -->
    @if ($showBannedModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true" x-data="{ show: false }" x-init="$nextTick(() => show = true)" x-show="show" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
            <div class="flex min-h-screen items-end justify-center px-4 pb-20 pt-4 text-center sm:block sm:p-0">

                <!-- Center modal -->
                <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

                <!-- Modal panel -->
                <div class="inline-block transform overflow-hidden rounded-lg bg-white text-left align-bottom shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg sm:align-middle dark:bg-gray-800" x-show="show" x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" @click.away="$wire.set('showBannedModal', false)">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4 dark:bg-gray-800">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 dark:bg-red-900/20">
                                <x-heroicon-m-exclamation-triangle class="h-6 w-6 text-red-600 dark:text-red-400" />
                            </div>
                            <div class="mt-3 w-full text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 id="modal-title" class="text-lg font-semibold leading-6 text-gray-900 dark:text-white">
                                    {{ __('partner/bill.ban_partner_title') }}
                                </h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('partner/bill.ban_partner_description') }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 dark:bg-gray-900/20">
                        <button class="inline-flex w-full justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 sm:ml-3 sm:w-auto" type="button" wire:click="closeBannedModal">
                            {{ __('global.close') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>
