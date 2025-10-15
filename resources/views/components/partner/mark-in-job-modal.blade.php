{{-- Modal: Mark as In Job --}}
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-show="showMarkInJobModal" style="display: none;" @click.self="closeModals()" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative max-w-md rounded-xl border border-gray-200 bg-white p-6 shadow-2xl dark:bg-gray-800" @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
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
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    {{ __('global.processing') }}...
                </span>
            </button>
        </div>
    </div>
</div>
