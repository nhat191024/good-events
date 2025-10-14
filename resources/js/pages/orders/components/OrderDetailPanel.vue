<script setup lang="ts">
// note: panel chi tiết bên phải. mobile sẽ có nút back để quay về danh sách
import BookingSummaryCard from './BookingSummaryCard.vue'
import ApplicantCard from './ApplicantCard.vue'
import { ArrowLeft, Star } from 'lucide-vue-next'
import { ClientOrder, ClientOrderDetail, OrderDetailStatus, OrderStatus, Partner } from '../types';
import BookingSummaryCardEmpty from './BookingSummaryCardEmpty.vue';
import { computed, ref } from 'vue';
import ReloadButton from './ReloadButton.vue';
import { debounce } from '../helper';
import { cn } from '@/lib/utils';

const props = withDefaults(defineProps<{
    mode?: 'current' | 'history'
    order?: ClientOrder | null
    applicants?: ClientOrderDetail[]
}>(), {
    mode: 'current',
    order: null,
    applicants: () => [],
})

const description = computed(() => {
    return (props.mode === 'current')
        ? 'Vui lòng đợi một lúc, chúng tôi đang gửi thông báo đến cho các đối tác gần đó và sẽ tải lại trang cho bạn'
        : 'Bạn đang xem lịch sử, đối tác đã từng được bạn chốt đơn sẽ hiển thị ở đây. Bạn cũng có thể đánh giá trải nghiệm của mình bên dưới'
});

const emit = defineEmits<{
    (e: 'back'): void,
    (e: 'rate'): void,
    (e: 'reload-detail',
    orderId: number | undefined): void
    (e: 'cancel-order') : void
    (e: 'confirm-choose-partner', partner?: Partner | null | undefined, total?: number | null | undefined) : void
    (e: 'view-partner-profile', partnerId: number): void
}>()

const bookedPartner = computed<ClientOrderDetail | undefined>(() => {
    return props.applicants.find(item => item.status === OrderDetailStatus.CLOSED)
})

const isReloading = ref(false)

const reloadOrderDetails = debounce(() => {
    isReloading.value = true
    emit('reload-detail', props.order?.id)
    setTimeout(() => {
        isReloading.value = false
    }, 10000)
}, 5000)

const classIfBookedPartnerFound = computed(()=>{
    return (bookedPartner && props.mode === 'current' && (props.order?.status==OrderStatus.CONFIRMED || props.order?.status==OrderStatus.IN_JOB)) ? 'hidden' : '';
});

</script>

<template>
    <div class="flex-1 overflow-y-auto h-full">
        <div class="mt-14 md:mt-6 px-1 md:px-3">
            <div class="md:hidden mb-3 md:static fixed top-18 pt-2">
                <button
                    class="inline-flex items-center gap-2 text-sm px-3 h-9 rounded-xl border border-border bg-white/30 backdrop-blur-md shadow-sm"
                    @click="emit('back')">
                    <ArrowLeft class="h-4 w-4" />
                    Quay lại danh sách
                </button>
            </div>

            <div class="mx-3 mb-4 flex">
                <h2 class="font-lexend text-2xl font-bold text-foreground mb-0 mr-1">
                    Chi tiết đơn hàng {{ props.order ? ' - ' + props.order.code : '' }}
                </h2>
                <!-- <p class="text-muted-foreground">{{ description }}</p> -->
                <ReloadButton :is-reloading="isReloading" @reload="reloadOrderDetails()" />
            </div>

            <template v-if="order">
                <div v-if="props.mode === 'current'" :class="cn('border-2 border-primary/20 rounded-xl bg-card p-3 md:p-3', classIfBookedPartnerFound)">
                    <div class="grid gap-2 md:gap-3">
                        <p v-text="description" class="text-secondary text-sm md:text-md"></p>
                        <div v-if="props.applicants.length > 0" class="md:hidden block md:mt-0 mt-2 md:mb-0 mb-3">
                            <hr>
                        </div>
                        <ApplicantCard @view-partner-profile="emit('view-partner-profile', $event)" :show-buttons="props.order?.status !=  OrderStatus.CONFIRMED && props.order?.status != OrderStatus.IN_JOB" v-for="a in props.applicants" :key="a.id" v-bind="a" @confirm-choose-partner="(partner, total) => emit('confirm-choose-partner', partner, total)"/>
                    </div>
                </div>
                <div v-else class="border-2 border-primary/20 rounded-xl bg-card p-3 md:p-5">
                    <div class="grid gap-2 md:gap-3">
                        <p v-text="description" class="text-secondary text-sm md:text-md"></p>
                        <div v-if="bookedPartner" class="md:hidden block md:mt-0 mt-2 md:mb-0 mb-3">
                            <hr>
                        </div>
                        <ApplicantCard @view-partner-profile="emit('view-partner-profile', $event)" :show-buttons="false" v-if="bookedPartner" v-bind="bookedPartner" />
                        <!-- rating button chỉ hiện ở history + completed -->
                        <!-- <div class="bg-white fixed bottom-1 md:bottom-3 w-[90%] md:w-[45%] lg:w-[55%] justify-self-center"> -->
                            <button v-if="bookedPartner && props.mode === 'history' && !props.order?.review"
                                class="z-10 fixed bottom-[3vh] w-[90%] md:w-[45%] lg:w-[55%] justify-self-center h-10 rounded-md border border-yellow-700 bg-yellow-400 hover:bg-yellow-500 active:bg-yellow-600 flex-1 inline-flex items-center justify-center gap-2"
                                @click="emit('rate')">
                                <Star class="h-4 w-4 text-white stroke-yellow-800 fill-white" />
                                <span class="font-bold text-yellow-950">Đánh giá</span>
                            </button>
                        <!-- </div> -->
                    </div>
                </div>
                <BookingSummaryCard @view-partner-profile="emit('view-partner-profile', $event)" :mode="props.mode" :booked-partner="bookedPartner" :order="props.order" class="mt-6" @cancel-order="emit('cancel-order')" />

            </template>
            <template v-else>
                <BookingSummaryCardEmpty />
            </template>
        </div>
    </div>
</template>
