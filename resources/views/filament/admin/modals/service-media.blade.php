@if ($mediaItems->isEmpty())
    <x-filament::section>
        <div style="text-align: center; padding: 2rem 0;">
            <x-filament::icon icon="heroicon-o-video-camera" style="width: 3rem; height: 3rem; margin: 0 auto; opacity: 0.5;" />
            <p style="margin-top: 1rem; opacity: 0.7;">
                Không có video nào cho dịch vụ này.
            </p>
        </div>
    </x-filament::section>
@else
    <div style="display: grid; gap: 0.75rem;">
        @foreach ($mediaItems as $media)
            <x-filament::section>
                <div style="display: flex; align-items: start; gap: 1rem;">
                    <div style="flex-shrink: 0; width: 3rem; height: 3rem; background: var(--primary-50); border-radius: 0.5rem; display: flex; align-items: center; justify-content: center;">
                        <x-filament::icon icon="heroicon-o-video-camera" style="width: 1.5rem; height: 1.5rem; color: var(--primary-600);" />
                    </div>

                    <div style="flex: 1; min-width: 0;">
                        <h3 style="font-weight: 600; margin-bottom: 0.5rem;">
                            {{ $media->name }}
                        </h3>

                        @if ($media->description)
                            <p style="font-size: 0.875rem; opacity: 0.7; margin-bottom: 0.75rem;">
                                {{ $media->description }}
                            </p>
                        @endif

                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem;">
                                <x-filament::icon icon="heroicon-m-link" style="width: 1rem; height: 1rem; opacity: 0.5;" />
                                <a href="{{ $media->url }}" target="_blank" style="color: var(--primary-600); text-decoration: none; word-break: break-all;">
                                    {{ $media->url }}
                                </a>
                                <button onclick="navigator.clipboard.writeText('{{ $media->url }}'); alert('Đã copy link!');" style="padding: 0.25rem 0.5rem; background: var(--foreground); border: 1px solid var(--gray-300); border-radius: 0.375rem; font-size: 0.75rem; cursor: pointer;">
                                    Copy
                                </button>
                            </div>

                            <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.75rem; opacity: 0.6;">
                                <x-filament::icon icon="heroicon-m-clock" style="width: 0.875rem; height: 0.875rem;" />
                                {{ $media->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>
            </x-filament::section>
        @endforeach
    </div>

    <x-filament::section style="margin-top: 1rem; background: var(--foreground);">
        <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.875rem;">
            <span style="opacity: 0.8;">
                Tổng số media: <strong>{{ $mediaItems->count() }}</strong>
            </span>
        </div>
    </x-filament::section>
@endif
