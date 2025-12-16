<x-filament-panels::page>
    @if ($bill)
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left Column: Bill Details --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Bill Info Card --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('partner/bill.bill_info') }}
                        </h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 px-6 py-4 md:grid-cols-2">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.bill_code') }}</p>
                            <p class="mt-1 font-mono text-sm text-gray-900 dark:text-white">{{ $bill->code }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.status') }}</p>
                            <div class="mt-1">
                                @php
                                    $statusColor = match ($bill->status->value) {
                                        'pending' => 'warning',
                                        'confirmed' => 'success',
                                        'in_job' => 'info',
                                        'paid' => 'success',
                                        'cancelled' => 'danger',
                                        default => 'gray',
                                    };
                                    $statusLabel = match ($bill->status->value) {
                                        'pending' => __('partner/bill.status_pending'),
                                        'confirmed' => __('partner/bill.status_confirmed'),
                                        'in_job' => __('partner/bill.status_in_job'),
                                        'paid' => __('partner/bill.paid'),
                                        'cancelled' => __('partner/bill.status_cancelled'),
                                        default => $bill->status->value,
                                    };
                                @endphp
                                <x-filament::badge :color="$statusColor">
                                    {{ $statusLabel }}
                                </x-filament::badge>
                            </div>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.date') }}</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $bill->date ? $bill->date->format('d/m/Y') : '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.time') }}</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $bill->start_time ? $bill->start_time->format('H:i') : '-' }} - {{ $bill->end_time ? $bill->end_time->format('H:i') : '-' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Items / Details --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('partner/bill.bill') }}
                        </h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                <tr>
                                    <th class="px-6 py-3 text-right">{{ __('partner/bill.price') }}</th>
                                    <th class="px-6 py-3 text-right">{{ __('partner/bill.total') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                @foreach ($bill->details as $detail)
                                    <tr class="bg-white dark:bg-gray-900">
                                        <td class="px-6 py-4 text-right">{{ number_format($detail->price) }} đ</td>
                                        <td class="px-6 py-4 text-right font-semibold">{{ number_format($detail->total) }} đ</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50 dark:bg-gray-800/50">
                                <tr>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">{{ __('partner/bill.total_amount') }}</td>
                                    <td class="text-primary-600 dark:text-primary-400 px-6 py-4 text-right text-lg font-bold">
                                        {{ number_format($bill->details->sum('total')) }} đ
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            {{-- Right Column: Client & Event Info --}}
            <div class="space-y-6">
                {{-- Client Info --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('partner/bill.client_info') }}
                        </h3>
                    </div>
                    <div class="space-y-4 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <x-filament::avatar src="{{ $bill->client->avatar_url ?? null }}" alt="{{ $bill->client->name ?? 'Client' }}" size="lg" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $bill->client->name ?? 'N/A' }}</p>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $bill->client->email ?? '' }}</p>
                            </div>
                        </div>
                        <div class="border-t border-gray-100 pt-4 dark:border-gray-800">
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.phone') }}</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $bill->phone }}</p>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.address') }}</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">{{ $bill->address }}</p>
                        </div>
                    </div>
                </div>

                {{-- Event Info --}}
                <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ __('partner/bill.event_info') }}
                        </h3>
                    </div>
                    <div class="space-y-4 px-6 py-4">
                        <div>
                            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.event_type') }}</p>
                            <p class="mt-1 text-sm text-gray-900 dark:text-white">
                                {{ $bill->event ? $bill->event->name : $bill->custom_event ?? 'N/A' }}
                            </p>
                        </div>
                        @if ($bill->note)
                            <div>
                                <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('partner/bill.note') }}</p>
                                <p class="mt-1 text-sm italic text-gray-900 dark:text-white">
                                    "{{ $bill->note }}"
                                </p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end">
                    <x-filament::button color="gray" tag="a" href="{{ redirect()->back()->getTargetUrl() }}">
                        {{ __('partner/bill.back_to_list') }}
                    </x-filament::button>
                </div>
            </div>
        </div>
    @else
        <div class="py-12 text-center">
            <p class="text-gray-500">{{ __('partner/bill.bill_not_found') }}</p>
        </div>
    @endif
</x-filament-panels::page>
