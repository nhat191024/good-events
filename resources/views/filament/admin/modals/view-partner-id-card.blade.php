@if (!$record->partnerProfile->identity_card_number)
    <x-filament::section>
        <div class="pt-2 text-center">
            <x-filament::icon class="m-auto h-12 w-12 opacity-50" icon="heroicon-o-video-camera" />
            <p class="mt-4 opacity-75">
                Đối tác chưa tải lên giấy tờ tùy thân.
            </p>
        </div>
    </x-filament::section>
@else
    <div class="flex w-full flex-col items-center justify-center gap-2">

        <x-filament::section>
            <div class="w-2xl flex flex-col items-center justify-center">
                <p>Ảnh selfie</p>
                <img class="mt-1 max-h-96 w-fit max-w-2xl" src="{{ asset($record->partnerProfile->selfie_image) }}" alt="selfie image" />
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="w-2xl flex flex-col items-center justify-center">
                <p>Số căn cước công dân</p>
                <p class="text-lg font-medium">{{ $record->partnerProfile->identity_card_number }}</p>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="w-2xl flex flex-col items-center justify-center">
                <p>Ảnh CCCD mặt trước</p>
                <img class="mt-1 w-fit max-w-2xl" src="{{ asset($record->partnerProfile->front_identity_card_image) }}" alt="front id card image" />
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="w-2xl flex flex-col items-center justify-center">
                <p>Ảnh CCCD mặt sau</p>
                <img class="mt-1 w-fit max-w-2xl" src="{{ asset($record->partnerProfile->back_identity_card_image) }}" alt="back id card image" />
            </div>
        </x-filament::section>
    </div>
@endif
