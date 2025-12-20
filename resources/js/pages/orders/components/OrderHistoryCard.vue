<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { ClientOrderHistory } from '../types';
import { statusBadge } from '../helper';
import { formatDate, formatPrice, formatTime } from '@/lib/helper';
import { Star } from 'lucide-vue-next';

const orders = withDefaults(defineProps<ClientOrderHistory & { selected?: boolean }>(), {
    selected: false,
})
</script>

<template>
    <div :class="[
        'relative cursor-pointer transition-shadow border rounded-lg bg-card',
        orders.selected ? 'border-primary-500 shadow-lg ring-1 ring-primary-400/60' : 'border-border hover:shadow-md'
    ]">
        <span v-if="orders.selected"
            class="absolute right-3 top-3 inline-flex items-center gap-1 rounded-full bg-primary-700 text-white px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide">
            Đang xem
        </span>
        <div class="p-4">
            <div class="flex items-start gap-3">
                <div class="h-10 w-10 rounded-full overflow-hidden bg-muted grid place-items-center">
                    <ImageWithLoader :src="getImg(orders.category?.image)" alt="org" class="h-10 w-10 rounded-full"
                        img-class="h-full w-full object-cover" loading="lazy" />
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="font-semibold text-card-foreground text-sm mb-1">{{ orders.category?.name ?? '' }}</h3>
                    <!-- <p class="text-xs text-muted-foreground mb-1">{{ orders.category?.parent?.name ?? '' }}</p> -->
                    <div class="flex items-center gap-2 mb-2">

                        <span class="text-xs text-muted-foreground">Tạo đơn lúc</span>
                        <span class="text-xs text-muted-foreground"></span>
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
                            <span>{{ orders.review?.rating ?? 'Chưa rate' }}</span>
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
