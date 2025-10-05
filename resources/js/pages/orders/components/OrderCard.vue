<script setup lang="ts">
import { getFirstImg } from '@/pages/booking/helper';
import { ClientOrder, OrderStatus } from '../types';
import { formatDate, formatPrice, formatTime, formatTimeRange } from '@/lib/helper';
import { statusBadge } from '../helper';

// note: card cho đơn hiện tại

const props = defineProps<ClientOrder>()
console.log("in order card",props);
</script>

<template>
    <div
        class="cursor-pointer transition-shadow border border-border rounded-lg hover:shadow-md bg-card border-l-4 border-l-primary-700">
        <div class="px-4 py-2 md:py-4">
            <div class="flex items-start gap-3">
                <div
                    class="h-12 w-12 rounded-full overflow-hidden bg-muted grid place-items-center ring-2 ring-primary/10">
                    <img :src="getFirstImg(props.category?.media)" alt="org" class="h-full w-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-card-foreground text-sm mb-0 md:1">{{ props.category.name }}</h3>
                    <p class="text-xs text-muted-foreground mb-1">{{ props.category?.parent?.name ?? '' }} • Số lượng ứng viên: {{ props.partners.count }}</p>
                    <p v-if="props.note" class="text-xs text-muted-foreground mb-1 md:mb-2">{{ props.note }}</p>
                    <div class="flex items-center gap-2 mb-1 md:mb-2">
                        <!-- <span class="text-xs px-2 py-0.5 rounded border border-border">{{ applicants ?? 0 }} ứng viên</span> -->

                        <!-- <span class="text-xs text-muted-foreground">•</span> -->
                        <span class="text-xs text-muted-foreground">Đặt lúc {{ formatTime(props.created_at ?? '')}} - {{ formatDate(props.created_at ?? '') }}</span>
                        <!-- <span class="text-xs text-muted-foreground">•</span>
                        <span class="text-xs text-muted-foreground">{{ formatDate(props.created_at ?? '') }}</span> -->
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-primary-700">Giá dự kiến: {{ formatPrice(props.final_total ?? 0) }} ₫</span>
                        <!-- <div v-text="2" class="h-4 w-4 bg-muted rounded" /> -->
                        <span class="text-xs px-2 py-0.5 rounded" :class="statusBadge(props.status).cls">
                            {{ statusBadge(props.status).text }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
