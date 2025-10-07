<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { ClientOrderHistory, OrderStatus } from '../types';
import { statusBadge } from '../helper';
import { formatDate, formatPrice, formatTime } from '@/lib/helper';
import { Star } from 'lucide-vue-next';

const orders = withDefaults(defineProps<ClientOrderHistory>(), {
})

console.log('order hjsito9ry card ', orders);
</script>

<template>
    <div class="cursor-pointer hover:shadow-md transition-shadow border border-border rounded-lg bg-card">
        <div class="p-4">
            <div class="flex items-start gap-3">
                <div class="h-10 w-10 rounded-full overflow-hidden bg-muted grid place-items-center">
                    <img :src="getImg(orders.category?.image)" alt="org" class="h-full w-full object-cover" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-card-foreground text-sm mb-1">{{ orders.category?.name ?? '' }}</h3>
                    <p class="text-xs text-muted-foreground mb-1">{{ orders.category?.parent?.name ?? '' }}</p>
                    <div class="flex items-center gap-2 mb-2">

                        <span class="text-xs text-muted-foreground">{{ orders.code }}</span>
                        <span class="text-xs text-muted-foreground">•</span>
                        <span class="text-xs text-muted-foreground">{{ formatTime(orders.created_at ?? '') }}</span>
                        <span class="text-xs text-muted-foreground">•</span>
                        <span class="text-xs text-muted-foreground">{{ formatDate(orders.created_at ?? '') }}</span>
                    </div>
                    <div v-if="orders" class="flex items-center gap-1 mb-2 text-xs">
                        <span class="text-muted-foreground">Đối tác nhận:</span>
                        <span class="font-medium">
                            {{ partner?.partner_profile?.partner_name ?? partner?.name ?? 'Không' }}
                        </span>

                        <span v-if="orders" class="inline-flex items-center gap-1 ml-2">
                            <Star class="h-3 w-3 fill-yellow-300 stroke-yellow-500" />
                            <span>{{ orders.partner?.statistics?.average_stars ?? 'Chưa rate' }}</span>
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-primary">{{ formatPrice(orders.final_total ?? 0) }}
                            ₫</span>
                        <span class="text-xs px-2 py-0.5 rounded" :class="statusBadge(orders.status).cls">{{
                            statusBadge(orders.status).text }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
