{{-- Modal: Mark as In Job --}}
<div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" x-data="{
    photoPreview: null,
    hasPhoto: false,
    handlePhotoUpload(event) {
        const file = event.target.files[0];
        if (file) {
            this.hasPhoto = true;
            const reader = new FileReader();
            reader.onload = (e) => {
                this.photoPreview = e.target.result;
            };
            reader.readAsDataURL(file);
        }
    },
    clearPhoto() {
        this.photoPreview = null;
        this.hasPhoto = false;
        $refs.photoInput.value = '';
        @this.set('arrivalPhoto', null);
    }
}" x-show="showMarkInJobModal" x-on:close-modal.window="closeModals(); clearPhoto()" style="display: none;" @click.self="closeModals()" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0">
    <div class="relative max-w-md mx-4 max-h-[90vh] flex flex-col rounded-xl border border-gray-200 bg-white shadow-2xl dark:bg-gray-800" @click.stop x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-90">
        {{-- Header --}}
        <div class="flex-shrink-0 p-6 pb-0">
        <div class="mb-4 flex items-start justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                    {{ __('partner/bill.mark_as_arrived') }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('partner/bill.mark_in_job_confirm') }}
                </p>
            </div>
            <button class="rounded-lg p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300" @click="closeModals(); clearPhoto()">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        </div>

        {{-- Content with scroll --}}
        <div class="flex-1 overflow-y-auto px-6">
        <div class="mb-6 space-y-4">
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

            {{-- Photo Upload Section --}}
            <div>
                <label class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                    {{ __('partner/bill.arrival_photo') }} <span class="text-red-500">*</span>
                </label>
                <div class="space-y-3">
                    {{-- Upload Button --}}
                    <div x-show="!hasPhoto" class="space-y-3">
                        <label class="flex cursor-pointer flex-col items-center rounded-lg border-2 border-dashed border-gray-300 bg-gray-50 p-6 transition-colors hover:border-blue-400 hover:bg-gray-100 dark:border-gray-600 dark:bg-gray-700 dark:hover:border-blue-500 dark:hover:bg-gray-600" for="arrival-photo">
                            <svg class="mb-2 h-10 w-10 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <span class="mb-1 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('partner/bill.click_to_upload_photo') }}
                            </span>
                            <span class="text-xs text-gray-500 dark:text-gray-400">
                                PNG, JPG, JPEG, WEBP ({{ __('partner/bill.max_5mb') }})
                            </span>
                        </label>
                        <input id="arrival-photo" class="hidden" type="file" x-ref="photoInput" accept="image/jpeg,image/png,image/jpg,image/webp" @change="handlePhotoUpload($event)" wire:model="arrivalPhoto" />

                        {{-- Camera Button --}}
                        <label class="flex w-full cursor-pointer items-center justify-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600" for="camera-photo">
                            <svg class="h-5 w-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            {{ __('partner/bill.take_photo') }}
                        </label>
                        <input id="camera-photo" class="hidden" type="file" accept="image/*" capture="environment" @change="handlePhotoUpload($event)" wire:model="arrivalPhoto" />
                    </div>

                    {{-- Photo Preview --}}
                    <div class="relative" x-show="hasPhoto" x-cloak>
                        <img class="h-48 w-full rounded-lg border border-gray-300 object-cover dark:border-gray-600" x-bind:src="photoPreview" alt="Preview" style="max-height: 200px;" />
                        <button class="absolute right-2 top-2 rounded-full bg-red-500 p-1.5 text-white shadow-lg transition-colors hover:bg-red-600" type="button" @click="clearPhoto()">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Validation Error --}}
                    @error('arrivalPhoto')
                        <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('partner/bill.arrival_photo_description') }}
                </p>
            </div>
        </div>
        </div>

        {{-- Actions --}}
        <div class="flex-shrink-0 p-6 pt-0">
        <div class="flex gap-3">
            <button class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 dark:hover:bg-gray-600" type="button"
                @click="closeModals(); clearPhoto()">
                {{ __('partner/bill.cancel') }}
            </button>
            <button class="bg-primary-600 hover:bg-primary-700 focus:ring-primary-500 flex-1 rounded-lg px-4 py-2 text-sm font-medium text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:bg-gray-400 disabled:hover:bg-gray-400" type="button"
                @click="$wire.markAsInJob(selectedBillId)" wire:loading.attr="disabled" wire:target="markAsInJob" x-bind:disabled="!hasPhoto">
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
</div>
