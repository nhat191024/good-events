@if (!$review)
    <x-filament::section>
        <div style="text-align:center; padding: 2rem 0;">
            <x-filament::icon icon="heroicon-o-star" style="width: 3rem; height: 3rem; margin: 0 auto; opacity: 0.5;" />
            <p style="margin-top: 1rem; opacity: 0.7;">Đơn này chưa có đánh giá.</p>
        </div>
    </x-filament::section>
@else
    <div style="display:flex; flex-direction:column; gap:1rem;">
        <x-filament::section>
            <div style="display:flex; align-items:center; justify-content:space-between; gap:1rem;">
                <div>
                    <div style="font-size:0.875rem; color:var(--gray-500);">Điểm đánh giá</div>
                    <div style="display:flex; align-items:center; gap:0.5rem; margin-top:0.25rem;">
                        <span style="font-size:1.5rem; font-weight:700;">{{ $rating ?? 'N/A' }}</span>
                        @if ($rating)
                            <span style="color:#f59e0b;">
                                @for ($i = 1; $i <= 5; $i++)
                                    {{ $i <= $rating ? '★' : '☆' }}
                                @endfor
                            </span>
                        @endif
                    </div>
                </div>

                <x-filament::badge :color="$review->recommend ? 'success' : 'gray'">
                    {{ $review->recommend ? 'Khuyến nghị' : 'Không khuyến nghị' }}
                </x-filament::badge>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div style="display:flex; flex-direction:column; gap:0.5rem;">
                <div style="font-size:0.875rem; font-weight:600;">Nhận xét</div>
                <div style="line-height:1.6; color:var(--gray-700);">
                    {{ filled($review->review) ? $review->review : 'Khách hàng không để lại nhận xét.' }}
                </div>
            </div>
        </x-filament::section>

        <div style="font-size:0.875rem; color:var(--gray-500);">
            Gửi {{ optional($review->created_at)->diffForHumans() }}
        </div>
    </div>
@endif
