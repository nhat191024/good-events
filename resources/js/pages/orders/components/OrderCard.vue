<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { ClientOrder, OrderStatus } from '../types';
import { calculateEstimatedPrice, formatDate, formatPrice, formatTime, formatTimeRange } from '@/lib/helper';
import { statusBadge } from '../helper';
import { cn } from '@/lib/utils';
import { MessageCircle } from 'lucide-vue-next';
import { router } from '@inertiajs/core';

// note: card cho đơn hiện tại

const props = withDefaults(defineProps<ClientOrder & { selected?: boolean }>(), {
    selected: false,
})

function getEstimatedPrice() {
    const price = props.final_total ?? props.total

    if (price && price > 0) {
        return formatPrice(price) + ' đ'
    }

    return 'Đợi báo giá'
}

function goToChat(thread_id: number) {
    if (!thread_id) return
    router.get(route('chat.index', { chat: thread_id }))
}

</script>

<template>
    <div :class="cn(
        'relative cursor-pointer transition-shadow border rounded-lg bg-card border-l-4',
        statusBadge(props.status).border_class,
        props.selected ? 'border-primary-500 shadow-lg ring-1 ring-primary-400/60' : 'hover:shadow-md'
    )">
        <span v-if="props.selected"
            class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-primary-700 text-white px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
            Đang xem
        </span>

        <div class="px-4 py-2 md:py-4">
            <div class="flex items-start gap-3 h-full">
                <div class="flex flex-col overflow-visible place-items-center h-full">
                    <div
                        class="h-12 w-12 grid rounded-full overflow-visible bg-muted place-items-center ring-2 ring-primary/10">
                        <ImageWithLoader :src="getImg(props.category?.image)" alt="org" class="h-12 w-12 rounded-full"
                            img-class="h-full w-full object-cover rounded-full" loading="lazy" />
                    </div>
                    <div v-if="props.status == OrderStatus.CONFIRMED || props.status == OrderStatus.IN_JOB"
                        class="h-12 w-12 grid rounded-full overflow-visible place-items-center">
                        <span
                            class="flex flex-col h-7 w-7 items-center justify-center rounded-full bg-primary-700 text-white shadow justify-self-start"
                            aria-label="Chat" @click="goToChat(props.thread_id)">
                            <MessageCircle class="h-4 w-4" aria-hidden="true" />
                        </span>
                    </div>

                    <div v-if="props.partners.count > 0 && props.status == OrderStatus.PENDING"
                        class="h-12 w-12 grid rounded-full overflow-visible place-items-center">
                        <span
                            class="text-sm px-2 py-[2px] ring-primary-700 bg-primary-700 text-white font-bold rounded-sm justify-self-start">{{
                                props.partners.count }}</span>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="mb-1 flex flex-row justify-between">
                        <h3 class="font-semibold text-card-foreground truncate text-sm mb-0 md:1">{{ props.category.name
                        }}</h3>
                    </div>
                    <p class="text-xs text-muted-foreground mb-1">Ở {{ props.address ?? '' }}</p>
                    <p v-if="props.note" class="text-xs text-muted-foreground mb-1 md:mb-2 truncate">
                        Ghi chú: {{ props.note }}
                    </p>
                    <div class="flex items-center gap-2 mb-1 md:mb-2">
                        <span class="text-xs text-muted-foreground">Tổ chức ngày {{ formatDate(props.date ?? '') }} từ
                            lúc {{ formatTime(props.start_time ?? '') }} đến {{ formatTime(props.end_time ?? '') }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-primary-700">
                            <template v-if="props.final_total ?? props.total">Giá chốt:</template>
                            <template v-else>Giá ước tính:</template>
                            {{ getEstimatedPrice() }}
                        </span>
                        <span class="text-xs px-2 py-0.5 rounded" :class="statusBadge(props.status).cls">
                            {{ statusBadge(props.status).text }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
