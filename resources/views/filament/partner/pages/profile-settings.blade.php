<x-filament-panels::page>
    <form wire:submit="save">
        {{ $this->form }}

        @php($serviceAreaRows = $this->getPaginatedServiceAreaRows())

        <x-filament::section class="mt-6">
            <x-slot name="heading">
                Danh sách vùng nhận đơn
            </x-slot>

            <x-slot name="description">
                Quản lý các phường/xã đã thêm. Nếu danh sách trống, hệ thống sẽ đẩy toàn bộ đơn đúng dịch vụ cho bạn.
            </x-slot>

            <div class="space-y-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $serviceAreaRows['total'] }} vùng đang được chọn
                    </p>

                    <x-filament::button
                        color="danger"
                        size="sm"
                        type="button"
                        wire:click="removeSelectedServiceAreas"
                        wire:loading.attr="disabled"
                        :disabled="empty($selectedServiceAreaTableIds)"
                    >
                        Xóa vùng đã chọn
                    </x-filament::button>
                </div>

                <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-800">
                    <table class="w-full divide-y divide-gray-200 text-sm dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="w-12 px-4 py-3 text-left">
                                    <span class="sr-only">Chọn</span>
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-200">
                                    Phường/xã
                                </th>
                                <th class="px-4 py-3 text-left font-medium text-gray-700 dark:text-gray-200">
                                    Tỉnh/thành
                                </th>
                                <th class="w-24 px-4 py-3 text-right font-medium text-gray-700 dark:text-gray-200">
                                    Thao tác
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-200 bg-white dark:divide-gray-800 dark:bg-gray-950">
                            @forelse ($serviceAreaRows['data'] as $serviceArea)
                                <tr wire:key="service-area-row-{{ $serviceArea['id'] }}">
                                    <td class="px-4 py-3">
                                        <input
                                            class="rounded border-gray-300 text-primary-600 shadow-sm focus:ring-primary-500 dark:border-gray-700 dark:bg-gray-900"
                                            type="checkbox"
                                            value="{{ $serviceArea['id'] }}"
                                            wire:model.live="selectedServiceAreaTableIds"
                                        />
                                    </td>
                                    <td class="px-4 py-3 font-medium text-gray-950 dark:text-white">
                                        {{ $serviceArea['name'] }}
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-gray-400">
                                        {{ $serviceArea['province_name'] ?? '—' }}
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <button
                                            class="text-sm font-medium text-red-600 hover:text-red-500 dark:text-red-400 dark:hover:text-red-300"
                                            type="button"
                                            wire:click="removeServiceArea({{ $serviceArea['id'] }})"
                                        >
                                            Xóa
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400" colspan="4">
                                        Chưa có vùng nhận đơn nào.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($serviceAreaRows['total'] > 0)
                    <div class="flex flex-col gap-3 border-t border-gray-200 pt-4 text-sm text-gray-600 dark:border-gray-800 dark:text-gray-400 sm:flex-row sm:items-center sm:justify-between">
                        <span>
                            Hiển thị {{ $serviceAreaRows['from'] }}-{{ $serviceAreaRows['to'] }} / {{ $serviceAreaRows['total'] }} vùng
                        </span>

                        <div class="flex items-center gap-2">
                            <x-filament::button
                                color="gray"
                                size="sm"
                                type="button"
                                wire:click="previousServiceAreaPage"
                                wire:loading.attr="disabled"
                                :disabled="$serviceAreaRows['current_page'] <= 1"
                            >
                                Trước
                            </x-filament::button>

                            <span class="px-2">
                                Trang {{ $serviceAreaRows['current_page'] }} / {{ $serviceAreaRows['last_page'] }}
                            </span>

                            <x-filament::button
                                color="gray"
                                size="sm"
                                type="button"
                                wire:click="nextServiceAreaPage"
                                wire:loading.attr="disabled"
                                :disabled="$serviceAreaRows['current_page'] >= $serviceAreaRows['last_page']"
                            >
                                Sau
                            </x-filament::button>
                        </div>
                    </div>
                @endif
            </div>
        </x-filament::section>
    </form>
</x-filament-panels::page>
