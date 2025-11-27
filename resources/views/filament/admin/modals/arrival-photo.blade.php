@if (!$media)
    <x-filament::section>
        <div style="text-align:center; padding: 2rem 0;">
            <x-filament::icon icon="heroicon-o-photo" style="width: 3rem; height: 3rem; margin: 0 auto; opacity: 0.5;" />
            <p style="margin-top: 1rem; opacity: 0.7;">Không có ảnh đến nơi.</p>
        </div>
    </x-filament::section>
@else
    <div style="display:flex; flex-direction:column; align-items:center; gap:0.75rem;">
        <div style="width:100%; max-width:900px;">
            <img src="{{ $media->getUrl() }}" alt="Arrival Photo" style="width:100%; max-height:70vh; object-fit:contain; border-radius:0.5rem;" />
        </div>

        <div style="width:100%; max-width:900px; display:flex; justify-content:space-between; align-items:center; gap:0.5rem;">
            <div style="font-size:0.875rem; color:var(--muted);">
                <x-filament::icon icon="heroicon-m-clock" style="width:0.85rem; height:0.85rem; opacity:0.6;" />
                {{ optional($media->created_at)->diffForHumans() }}
            </div>

            <div style="display:flex; gap:0.5rem;">
                <a href="{{ $media->getUrl() }}" target="_blank" style="padding:0.4rem 0.65rem; background:var(--primary-600); color:white; border-radius:0.375rem; text-decoration:none; font-size:0.875rem;">Mở trong tab mới</a>
                <a href="{{ $media->getUrl() }}" download style="padding:0.4rem 0.65rem; border:1px solid var(--gray-300); border-radius:0.375rem; font-size:0.875rem;">Tải xuống</a>
            </div>
        </div>
    </div>
@endif
