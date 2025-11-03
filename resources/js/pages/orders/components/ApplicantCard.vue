<script setup lang="ts">
import { Award, Star, MapPin, Clock } from 'lucide-vue-next'
import { ClientOrderDetail, Partner } from '../types';
import { formatPrice } from '@/lib/helper';
import { router } from '@inertiajs/core';
import { getImg } from '@/pages/booking/helper';

const props = withDefaults(defineProps<ClientOrderDetail & {
    showButtons?: boolean
}>(), {
    showButtons: true,
})

const emit = defineEmits<{
    (e: 'confirm-choose-partner', partner: Partner | null | undefined, total: number | null | undefined): void
    (e: 'view-partner-profile', partnerId: number): void
}>()

function goToPartnerProfile() {
    if (!props.partner) return
    emit('view-partner-profile', props.partner.id)
}
</script>

<template>
    <div class="transition-all duration-200 border-2 rounded-xl bg-card hover:shadow-lg hover:border-primary/20">
        <div class="p-2 md:p-4">
            <div class="flex gap-6">
                <!-- content -->
                <div class="flex-1">
                    <div class="flex items-start justify-start mb-3 gap-3">
                        <!-- avatar -->
                        <div class="flex-shrink-0">
                            <div
                                class="h-15 md:h-20 w-15 md:w-20 rounded-full overflow-hidden ring-2 ring-primary/20 grid place-items-center bg-muted">
                                <img :src="getImg(props.partner?.avatar)"
                                    @error="(e: any) => e.target.src = getImg(undefined)" :alt="props.partner?.name"
                                    class="h-full w-full object-cover" />
                            </div>
                        </div>
                        <div class="w-full">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xl font-bold text-foreground">
                                    {{ partner.partner_profile?.partner_name ?? partner.name }}</h3>
                                <!-- <Award v-if="verified" class="h-5 w-5 text-primary" /> -->
                            </div>
                            <div class="flex flex-col items-start gap-1 text-sm text-muted-foreground mb-0">
                                <div class="flex items-center gap-1">
                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                    <span class="font-medium">{{ rating ?? '0' }}</span>
                                    <span>({{ totalJobs ?? '0' }} đánh giá)</span>
                                </div>
                                <div class="text-left flex flex-col">
                                    <div class="text-xs text-muted-foreground">Giá đối tác đề xuất</div>
                                    <div class="xs:text-md font-bold text-primary-700 mb-1">
                                        {{ formatPrice(props.total ?? 0) }} ₫
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                    <!-- stats -->
                    <div class="grid grid-cols-2 gap-4 mb-4">
                        <div class="text-center p-3 bg-muted/50 rounded-lg">
                            <div class="text-lg font-bold text-foreground">{{ completionRate ?? 100 }}%</div>
                            <div class="text-xs text-muted-foreground">Tỷ lệ hoàn thành</div>
                        </div>
                        <div class="text-center p-3 bg-muted/50 rounded-lg">
                            <div class="flex items-center justify-center gap-1">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <div class="text-sm font-medium text-foreground">{{ responseTime ?? '0%' }}</div>
                            </div>
                            <div class="text-xs text-muted-foreground">Tỉ lệ hủy</div>
                        </div>
                    </div>
                    <!-- actions -->
                    <div class="flex items-end sm:gap-2 gap-1 justify-end">
                        <button @click="goToPartnerProfile()"
                            class="cursor-pointer h-10 rounded-md border border-primary-700 text-primary-700 text-sm md:text-md bg-transparent md:px-6 px-2 hover:bg-primary/5">
                            Hồ sơ
                        </button>
                        <button v-if="showButtons" @click="emit('confirm-choose-partner', props.partner, props.total)"
                            class="cursor-pointer h-10 rounded-md bg-primary-700 text-white md:px-6 px-3 text-md md:text-md">
                            Chọn đối tác
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
