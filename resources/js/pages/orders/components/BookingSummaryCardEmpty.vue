<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { Star } from 'lucide-vue-next';

</script>

<template>
    <div class="border-2 border-gray-300 rounded-xl bg-card">
        <div class="p-3 md:p-6">
            <!-- mobile back button sẽ nằm ở panel ngoài -->
            <div class="text-center mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-gray-100 rounded-full mb-3">
                    <img :src="getImg('')"
                        alt="Traditional Vietnamese scholar" class="w-12 h-12 object-contain rounded-full" />
                </div>
                <h3 class="text-xl font-bold text-foreground mb-1">Xem chi tiết</h3>
                <p class="text-sm text-muted-foreground">Hãy chọn một đơn bất kỳ để xem chi tiết
                </p>
            </div>

            <div class="max-w-3xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-4 mb-3 md:mb-6">
                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <CalendarDays class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Ngày sự kiện</div>
                            <div class="text-sm md:text-md font-medium">-</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <Clock class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Thời gian</div>
                            <div class="text-sm md:text-md font-medium">-</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <MapPin class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Địa điểm</div>
                            <div class="text-sm md:text-md font-medium">-</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <Award class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Loại dịch vụ</div>
                            <div class="text-sm md:text-md font-medium">-</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 mb-6">
                    <div>
                        <div class="text-xs text-muted-foreground mb-2">Ghi chú đặc biệt</div>
                        <span class="text-sm px-3 py-1 rounded border border-border">-</span>
                    </div>
                    <!-- <div>
                        <div class="text-xs text-muted-foreground mb-2">Địa chỉ tổ chức</div>
                        <div class="text-sm text-foreground">{{ props.order?.address ?? '' }}
                        </div>
                    </div> -->
                </div>

                <!-- actions -->
                <div class="flex gap-3">
                    <button disabled class="h-10 rounded-md bg-gray-400 text-white flex-1">Chat now</button>

                    <button
                        class="h-10 rounded-md border border-gray-400 text-gray-400 bg-transparent flex-1">
                        Cancel
                    </button>

                    <!-- rating button chỉ hiện ở history + completed -->
                    <button disabled
                        class="h-10 rounded-md border border-gray-400 flex-1 inline-flex items-center justify-center gap-2"
                        >
                        <Star class="h-4 w-4 text-gray-400" />
                        <span class="text-gray-400">Đánh giá</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
