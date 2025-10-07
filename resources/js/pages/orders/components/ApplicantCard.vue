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
console.log('appliant ', props);

const emit = defineEmits<{
    (e: 'confirm-choose-partner', partner: Partner | null | undefined) : void
}>()

function goToPartnerProfile(){
    console.log(JSON.stringify(props));
    console.log(props.partner);

    if ( !props.partner ) return
    const partner = props.partner
    router.get(route('profile.partner.show', { user: partner.id }))

}
</script>

<template>
    <div class="transition-all duration-200 border-2 rounded-xl bg-card hover:shadow-lg hover:border-primary/20">
        <div class="p-3 md:p-6">
            <div class="flex gap-6">
                <!-- content -->
                <div class="flex-1">
                    <div class="flex items-start justify-start mb-3 gap-3">
                        <!-- avatar -->
                        <div class="flex-shrink-0">
                            <div
                                class="h-15 md:h-20 w-15 md:w-20 rounded-full overflow-hidden ring-2 ring-primary/20 grid place-items-center bg-muted">
                                <img :src="getImg(props.partner?.avatar)"  @error="(e:any) => e.target.src = getImg(undefined)" :alt="props.partner?.name"
                                    class="h-full w-full object-cover" />
                            </div>
                        </div>
                        <div class="w-full">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-xl font-bold text-foreground">{{ partner.partner_profile?.partner_name ?? partner.name }}</h3>
                                <!-- <Award v-if="verified" class="h-5 w-5 text-primary" /> -->
                            </div>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground mb-2">
                                <div class="flex items-center gap-1">
                                    <Star class="h-4 w-4 fill-yellow-400 text-yellow-400" />
                                    <span class="font-medium">{{ rating ?? '0' }}</span>
                                    <span>({{ totalJobs ?? '0' }} đánh giá)</span>
                                </div>
                                <!-- <div class="flex items-center gap-1">
                                    <MapPin class="h-4 w-4" />
                                    <span>{{ location ?? 'No' }}</span>
                                </div> -->
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
                            <div class="text-lg font-bold text-foreground">{{ totalJobs ?? 0 }}</div>
                            <div class="text-xs text-muted-foreground">Đơn hoàn thành</div>
                        </div>
                        <div class="text-center p-3 bg-muted/50 rounded-lg">
                            <div class="flex items-center justify-center gap-1">
                                <Clock class="h-4 w-4 text-muted-foreground" />
                                <div class="text-sm font-medium text-foreground">{{ responseTime ?? '0%' }}</div>
                            </div>
                            <div class="text-xs text-muted-foreground">Tỉ lệ hủy</div>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-muted-foreground">Giá do đối tác đề xuất</div>
                        <div class="text-2xl font-bold text-primary-700 mb-1">
                            {{ formatPrice(props.total ?? 0) }} ₫
                        </div>
                    </div>
                    <!-- skills -->
                    <div class="mb-4">
                        <div class="flex flex-wrap gap-2">
                            <span v-for="(s, i) in skills" :key="i"
                                class="text-xs rounded px-2.5 py-1 bg-secondary text-secondary-foreground">
                                {{ s }}
                            </span>
                        </div>
                    </div>
                    <!-- description -->
                    <!-- <p class="text-sm text-muted-foreground mb-4 leading-relaxed">{{ description ?? 'Hello' }}</p> -->
                    <!-- actions -->
                    <div v-if="showButtons" class="flex items-end gap-3 justify-end">
                        <button disabled
                            class="hidden cursor-pointer h-10 rounded-md text-muted-foreground  hover:text-foreground md:px-6 px-2 text-md md:text-md">
                            Chat
                        </button>
                        <button @click="goToPartnerProfile()"
                            class="cursor-pointer h-10 rounded-md border border-primary-700 text-primary-700 text-sm md:text-md bg-transparent md:px-6 px-3 hover:bg-primary/5">
                            Hồ sơ
                        </button>
                        <button @click="emit('confirm-choose-partner', props.partner)" class="cursor-pointer h-10 rounded-md bg-primary-700 text-white md:px-6 px-3 text-md md:text-md">
                            Chọn đối tác
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
