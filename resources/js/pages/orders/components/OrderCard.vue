<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { ClientOrder, OrderStatus } from '../types';
import { calculateEstimatedPrice, formatDate, formatPrice, formatTime, formatTimeRange } from '@/lib/helper';
import { statusBadge } from '../helper';
import { cn } from '@/lib/utils';

// note: card cho đơn hiện tại

const props = defineProps<ClientOrder>()
</script>

<template>
    <div
        :class="cn('cursor-pointer transition-shadow border border-border rounded-lg hover:shadow-md bg-card border-l-4', statusBadge(props.status).border_class)">
        <div class="px-4 py-2 md:py-4">
            <div class="flex items-start gap-3">
                <div
                    class="h-12 w-12 rounded-full overflow-hidden bg-muted grid place-items-center ring-2 ring-primary/10">
                    <img :src="getImg(props.category?.image)" alt="org" class="h-full w-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex flex-row justify-between">
                        <h3 class="font-semibold text-card-foreground text-sm mb-0 md:1">{{ props.category.name }}</h3>
                        <span v-if="props.partners.count>0 && props.status == OrderStatus.PENDING" class="text-sm px-2 py-[2px] ring-primary-700 bg-primary-700 text-white font-bold rounded-sm">{{ props.partners.count }}</span>
                    </div>
                    <p class="text-xs text-muted-foreground mb-1">{{ props.category?.parent?.name ?? '' }}</p>
                    <p v-if="props.note" class="text-xs text-muted-foreground mb-1 md:mb-2">{{ props.note }}</p>
                    <div class="flex items-center gap-2 mb-1 md:mb-2">
                        <span class="text-xs text-muted-foreground">Đặt lúc {{ formatTime(props.created_at ?? '')}} - {{ formatDate(props.created_at ?? '') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-primary-700">Tạm ước tính: {{ formatPrice(calculateEstimatedPrice(props.start_time, props.end_time, props.category.min_price, props.category.max_price) ?? 0) }} ₫</span>
                        <span class="text-xs px-2 py-0.5 rounded" :class="statusBadge(props.status).cls">
                            {{ statusBadge(props.status).text }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
