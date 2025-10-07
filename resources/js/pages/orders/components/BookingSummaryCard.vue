<script setup lang="ts">
// note: hiển thị tóm tắt + nút hành động. nếu là history & completed -> hiện nút đánh giá
import { CalendarDays, Clock, MapPin, Award } from 'lucide-vue-next'
import { ClientOrder } from '../types';
import { computed } from 'vue';
import { formatDate, formatTimeRange } from '@/lib/helper';
import { Input } from '@/components/ui/input';
import { getImg } from '@/pages/booking/helper';

const props = withDefaults(defineProps<{
    mode?: 'current' | 'history'
    order?: ClientOrder | null
}>(), {
    mode: 'current',
    order: undefined,
})

console.log('bookin summary card ', props.order);

const getCurrentTitle = computed(() => {
    if (props.mode == 'current') {
        return `Thông tin thuê chi tiết`
    } else {
        return `Chi tiết lịch sử đơn`
    }
})

const emit = defineEmits<{
    (e: 'cancel-order'): void
}>()

</script>

<template>
    <div class="border-2 border-primary/20 rounded-xl bg-card">
        <div class="p-3 md:p-6">
            <!-- mobile back button sẽ nằm ở panel ngoài -->
            <div class="text-center mb-3 md:mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-3">
                    <img :src="getImg(props.order?.category.image)"
                        alt="Traditional Vietnamese scholar" class="w-12 h-12 object-contain rounded-full" />
                </div>
                <h3 class="text-xl font-bold text-foreground mb-1" v-text="getCurrentTitle"></h3>
                <div v-if="props.order?.status"
                    class="inline-flex items-center justify-center px-3 py-1 mb-2 rounded-full text-xs font-medium"
                    :class="{
                        'bg-yellow-100 text-yellow-800 border border-yellow-200': props.order.status === 'pending',
                        'bg-blue-100 text-blue-800 border border-blue-200': props.order.status === 'paid',
                        'bg-red-100 text-red-800 border border-red-200': props.order.status === 'cancelled',
                        'bg-gray-100 text-gray-700 border border-gray-200': !['pending', 'paid', 'cancelled'].includes(props.order.status)
                    }">
                    <span v-if="props.order.status === 'pending'">Đang chờ xử lý</span>
                    <span v-else-if="props.order.status === 'paid'">Đã hoàn thành</span>
                    <span v-else-if="props.order.status === 'cancelled'">Đã hủy</span>
                    <span v-else>{{ props.order.status }}</span>
                </div>
                <p class="text-sm text-muted-foreground">Bạn đã chọn đối tác '{{ props.order?.category?.parent?.name ??
                    '' }} - {{ props.order?.category?.name ?? '' }}'
                </p>
            </div>

            <div class="max-w-3xl mx-auto">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-4 mb-3 md:mb-6">
                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <CalendarDays class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Ngày sự kiện</div>
                            <div class="text-sm md:text-md font-medium">{{ formatDate(props.order?.date ?? '') }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <Clock class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Thời gian</div>
                            <div class="text-sm md:text-md font-medium">{{ formatTimeRange(props.order?.start_time ??
                                '', props.order?.end_time ?? '')}}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <MapPin class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Địa điểm</div>
                            <div class="text-sm md:text-md font-medium">{{ props.order?.address ?? '' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <Award class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Loại dịch vụ</div>
                            <div class="text-sm md:text-md font-medium">{{ props.order?.event?.name ?? 'Không' }}</div>
                        </div>
                    </div>
                </div>

                <div class="space-y-3 mb-8">
                    <div>
                        <div class="text-xs text-muted-foreground mb-2">Ghi chú đặc biệt</div>
                        <span class="text-sm px-3 py-1 rounded border border-border">{{ props.order?.note ?? 'Không'
                            }}</span>
                    </div>
                    <div v-if="props.mode === 'current'">
                        <div class="text-xs text-muted-foreground mb-2">Mã Voucher giảm giá (Áp dụng vào giá của ứng
                            viên bạn chọn)</div>
                        <Input placeholder="VD: N1993+1..." value="" class="w-full md:w-1/2" />
                    </div>
                    <div v-else>
                        <div class="text-xs text-muted-foreground mb-2">Mã Voucher đã áp dụng</div>
                        <Input disabled placeholder="Không" class="w-full md:w-1/2" />
                    </div>
                    <!-- <div>
                        <div class="text-xs text-muted-foreground mb-2">Địa chỉ tổ chức</div>
                        <div class="text-sm text-foreground">{{ props.order?.address ?? '' }}
                        </div>
                    </div> -->
                </div>

                <!-- actions -->
                <div
                    class="flex gap-3 bg-white fixed bottom-1 md:bottom-3 w-[90%] md:w-[45%] lg:w-[55%] justify-self-center">
                    <button v-if="props.mode === 'current'" disabled
                        class="h-10 rounded-md bg-gray-500 text-white flex-1">Chat ngay</button>

                    <button v-if="props.mode === 'current'" @click="emit('cancel-order')"
                        class="h-10 rounded-md border border-destructive text-destructive bg-transparent flex-1 hover:bg-destructive/5">
                        Hủy đơn
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
