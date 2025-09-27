<x-filament-panels::page>
    <div class="space-y-6" wire:poll.30s="autoRefresh">
        <!-- Flash Messages -->
        @if (session()->has('success'))
            <div class="rounded-md bg-green-50 p-4 dark:bg-green-900/20">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <x-heroicon-s-check-circle class="h-5 w-5 text-green-400" />
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">
                            {{ session('success') }}
                        </p>
                    </div>
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
                    {{ __('partner/bill.last_updated') }}:{{ $lastUpdated ?? 'Never' }}
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
            <div class="border-b border-gray-200 bg-gray-50 px-4 py-4 sm:px-6 dark:border-gray-700 dark:bg-gray-700/50">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900 sm:text-lg dark:text-white">
                        {{ __('partner/bill.pending_orders') }} ({{ count($partnerBills) }})
                    </h3>
                </div>

                <!-- Filters Section - Stack on mobile -->
                <div class="mt-4 space-y-3 sm:grid sm:grid-cols-3 sm:gap-4 sm:space-y-0 lg:grid-cols-4">
                    <!-- Search -->
                    <div class="relative sm:col-span-2 lg:col-span-1">
                        <input class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white dark:placeholder-gray-400" type="text" placeholder="Tìm kiếm theo mã đơn, khách hàng..."
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
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" wire:model.live="dateFilter">
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
                        <select class="w-full rounded-md border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:border-gray-600 dark:bg-gray-700 dark:text-white" wire:model.live="categoryFilter">
                            <option value="all">Tất cả danh mục</option>
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
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['client']['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-tag class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">Danh mục:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['category']['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-calendar-days class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.event') }}:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['event']['name'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-clock class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.date') }}:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($bill['date'])->format('d/m/Y') }}</span>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-phone class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.phone') }}:</span>
                                            <span class="ml-1 text-gray-900 dark:text-white">{{ $bill['phone'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- Time Details for mobile -->
                                    @if ($bill['start_time'] || $bill['end_time'])
                                        <div class="flex flex-wrap gap-4">
                                            @if ($bill['start_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-play class="h-4 w-4 text-green-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.start_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($bill['start_time'])->format('H:i') }}</span>
                                                </div>
                                            @endif
                                            @if ($bill['end_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-stop class="h-4 w-4 text-red-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.end_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($bill['end_time'])->format('H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Address for mobile -->
                                    <div class="flex items-start gap-2">
                                        <x-heroicon-m-map-pin class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div>
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.address') }}:</span>
                                            <p class="mt-1 text-gray-900 dark:text-white">{{ $bill['address'] }}</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action button for mobile -->
                                <div class="mt-4">
                                    <button class="inline-flex w-full items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                        wire:click="acceptOrder({{ $bill['id'] }})">
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
                                            <span class="text-gray-900 dark:text-white">{{ $bill['client']['name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-tag class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">Danh mục:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $bill['category']['name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-calendar-days class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.event') }}:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $bill['event']['name'] ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-clock class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.date') }}:</span>
                                            <span class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($bill['date'])->format('d/m/Y') }}</span>
                                        </div>
                                        <div class="flex items-center gap-2">
                                            <x-heroicon-m-phone class="h-4 w-4 text-gray-400" />
                                            <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.phone') }}:</span>
                                            <span class="text-gray-900 dark:text-white">{{ $bill['phone'] ?? 'N/A' }}</span>
                                        </div>
                                    </div>

                                    <!-- Time Details Row for desktop -->
                                    @if ($bill['start_time'] || $bill['end_time'])
                                        <div class="mt-2 grid grid-cols-1 gap-3 text-sm md:grid-cols-2">
                                            @if ($bill['start_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-play class="h-4 w-4 text-green-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.start_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($bill['start_time'])->format('H:i') }}</span>
                                                </div>
                                            @endif
                                            @if ($bill['end_time'])
                                                <div class="flex items-center gap-2">
                                                    <x-heroicon-m-stop class="h-4 w-4 text-red-400" />
                                                    <span class="font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.end_time') }}:</span>
                                                    <span class="text-gray-900 dark:text-white">{{ \Carbon\Carbon::parse($bill['end_time'])->format('H:i') }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Address for desktop -->
                                    <div class="mt-3 flex items-start gap-2">
                                        <x-heroicon-m-map-pin class="mt-0.5 h-4 w-4 flex-shrink-0 text-gray-400" />
                                        <div class="flex">
                                            <span class="pe-2 text-sm font-medium text-gray-600 dark:text-gray-400">{{ __('partner/bill.address') }}:</span>
                                            <p class="text-sm text-gray-900 dark:text-white">{{ $bill['address'] }}</p>
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
                                        <button class="inline-flex items-center rounded-md bg-green-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm transition-colors hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2" wire:click="acceptOrder({{ $bill['id'] }})">
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
</x-filament-panels::page>
