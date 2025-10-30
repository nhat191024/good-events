<script setup lang="ts">

import { CalendarDays, Clock, MapPin, Award, StickyNote, Handshake, BadgeDollarSign, ArrowUpRightFromSquare, Star } from 'lucide-vue-next'
import { ClientOrder, ClientOrderDetail, OrderStatus } from '../types';
import { computed, onMounted, watch } from 'vue';
import { formatDate, formatPrice, formatTimeRange } from '@/lib/helper';
import { Input } from '@/components/ui/input';
import { getImg } from '@/pages/booking/helper';
import { router } from '@inertiajs/core';
import { statusBadge } from '../helper';
import Button from '@/components/ui/button/Button.vue';
import { useForm } from '@inertiajs/vue3';
import { confirm } from '@/composables/useConfirm';
import axios from 'axios';

const props = withDefaults(defineProps<{
    mode?: 'current' | 'history'
    order?: ClientOrder | null
    bookedPartner?: ClientOrderDetail | null
    modelValue?: string
}>(), {
    mode: 'current',
    order: undefined,
    modelValue: ''
})

const storageKey = computed(() => `voucher_${form.order_id}`)

const getCurrentTitle = computed(() => {
    if (props.mode == 'current') {
        return `Thông tin thuê chi tiết`
    } else {
        return `Chi tiết lịch sử đơn`
    }
})

const emit = defineEmits<{
    (e: 'cancel-order'): void
    (e: 'view-partner-profile', partnerId: number): void
    (e: 'update:modelValue', value: string): void
}>()

function goToChat(thread_id: number|null) {
    if (!thread_id) return
    router.get(route('chat.index', { chat: thread_id }))
}

function goToPartnerProfile() {
    if (!props.bookedPartner) return
    const partner = props.bookedPartner.partner
    if (!partner) return

    emit('view-partner-profile', partner.id)

}

function getEventType(order: ClientOrder | null | undefined) {
    if (!order) return 'Không'
    if (order.custom_event) {
        return order.custom_event;
    } else {
        return order.event?.name ?? 'Không'
    }
}

const form = useForm({
    voucher_input: '',
    order_id: props.order?.id ?? 0,
})

async function validateVoucher() {
    form.processing = true

    try {
        const { data } = await axios.post(route('client-orders.validate-voucher'), {
            voucher_input: form.voucher_input,
            order_id: form.order_id,
        }, {
            headers: { Accept: 'application/json' },
        })

        showNotice(data['status'] === true ? 'Kiểm tra mã giảm giá thành công!' : 'Mã giảm giá này chưa thể sử dụng được!', data['message']+'\n- Mã giảm giá đã được lưu tạm thời, hãy giữ nguyên mã khi bấm chốt đơn với đối tác nhé!');
    } catch (error) {
        showNotice('Có lỗi xảy ra!', 'Chưa thể áp dụng hoặc mã giảm giá chưa hợp lệ, vui lòng thử lại sau!');
    } finally {
        form.processing = false
    }
}

async function showNotice(title: string, message: string) {
    await confirm({
        title: title,
        message: message,
        okText: 'OK',
        cancelText: 'Quay lại'
    })
}

function loadVoucherFromStorage() {
    const saved = localStorage.getItem(storageKey.value)
    form.voucher_input = saved ?? ''
}

function getDiscountedAmountText(total: number | null | undefined, finalTotal: number | null | undefined) {
    if (!total || !finalTotal) return 'Không'
    const discounted = total - finalTotal
    if (discounted > 0) return 'Đã giảm: ' + formatPrice(discounted) + 'đ'
    else return 'Không'
}

onMounted(() => {
    loadVoucherFromStorage()
})

watch(() => props.order?.id, (newId) => {
    form.order_id = newId ?? 0
    loadVoucherFromStorage()
})

watch(() => form.voucher_input, (newVal) => {
    emit('update:modelValue', newVal)
    if (!form.order_id) return
    if (newVal && newVal.trim().length > 0) {
        localStorage.setItem(`voucher_${form.order_id}`, newVal)
    } else {
        localStorage.removeItem(`voucher_${form.order_id}`)
    }
})
</script>

<template>
    <div class="border-2 border-primary/20 rounded-xl bg-card">
        <div class="p-3 md:p-6">
            <div class="text-center mb-3 md:mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-3">
                    <img :src="getImg(props.order?.category.image)" alt="Traditional Vietnamese scholar"
                        class="w-[95%] h-full object-cover rounded-full border" />
                </div>
                <h3 class="text-xl font-bold text-foreground mb-1" v-text="getCurrentTitle"></h3>
                <div v-if="props.order?.status"
                    class="inline-flex items-center justify-center px-3 py-1 mb-2 rounded-full text-xs font-medium"
                    :class="statusBadge(props.order?.status).cls">
                    <span>{{ statusBadge(props.order?.status).text }}</span>
                </div>
                <p class="text-sm text-muted-foreground">
                    Bạn đã chọn đối tác
                    '{{ props.order?.category?.parent?.name ?? '' }} - {{ props.order?.category?.name ?? '' }}'
                </p>
            </div>

            <div class="max-w-3xl mx-auto">
                <div v-if="props.order?.review" class="space-y-3 mb-3">
                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <Star class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">
                                {{ 'Đánh giá của bạn '
                                    + (
                                        props.order?.review?.rating
                                            ? ('('
                                                + props.order?.review?.rating
                                                + 'sao)')
                                            : ('')
                                    )
                                }}
                            </div>
                            <div class="text-sm md:text-md font-medium">{{ props.order?.review?.comment ?? 'Không' }}
                            </div>
                        </div>
                    </div>
                </div>
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
                            <div class="text-sm md:text-md font-medium">
                                {{ formatTimeRange(props.order?.start_time ?? '', props.order?.end_time ?? '') }}
                            </div>
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
                            <div class="text-xs text-muted-foreground">Loại sụ kiện</div>
                            <div class="text-sm md:text-md font-medium">{{ getEventType(props.order) }}</div>
                        </div>
                    </div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-1 md:gap-4 mb-3 md:mb-6">
                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <StickyNote class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Ghi chú đặc biệt</div>
                            <div class="text-sm md:text-md font-medium">{{ props.order?.note ?? 'Không' }}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                        <Clock class="h-5 w-5 text-muted-foreground" />
                        <div>
                            <div class="text-xs text-muted-foreground">Thời gian tạo đơn</div>
                            <div class="text-sm md:text-md font-medium">
                                {{ formatDate(props.order?.created_at ?? '') }}
                            </div>
                        </div>
                    </div>

                    <template v-if="props.bookedPartner">
                        <div @click="goToPartnerProfile()"
                            class="flex items-center justify-between gap-3 p-1 md:p-3 bg-muted/30 rounded-lg cursor-pointer hover:bg-muted/50 transition">
                            <div class="flex items-center gap-3">
                                <Handshake class="h-5 w-5 text-muted-foreground" />
                                <div>
                                    <div class="text-xs text-muted-foreground">Đối tác đã chốt</div>
                                    <div class="flex items-center text-sm md:text-md font-medium gap-1 hover:underline">
                                        {{ bookedPartner?.partner?.partner_profile?.partner_name ??
                                            bookedPartner?.partner?.name }}
                                        <ArrowUpRightFromSquare class="h-3.5 w-3.5 text-muted-foreground" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-3 p-1 md:p-3 bg-muted/30 rounded-lg">
                            <BadgeDollarSign class="h-5 w-5 text-muted-foreground" />
                            <div>
                                <div class="text-xs text-muted-foreground">Giá niêm phong</div>
                                <div class="text-md md:text-lg font-medium text-red-700">
                                    {{ formatPrice(props.order?.final_total ?? 0) }} ₫
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <div class="space-y-3 mb-12 md:mb-8 lg:mb-12">
                    <div v-if="props.mode === 'current' && !props.bookedPartner">
                        <div class="flex flex-col">
                            <div class="text-xs text-muted-foreground mb-2">
                                Mã giảm giá (Áp dụng vào giá chốt của ứng viên bạn chọn)
                            </div>
                            <div class="relative flex gap-2 items-center">
                                <Input v-model="form.voucher_input" placeholder="VD: N1993+1..." class="w-full" />
                                <Button :disabled="form.processing" @click="validateVoucher"
                                    class="text-white font-bold h-full">
                                    Kiểm tra & Lưu mã
                                </Button>
                            </div>
                        </div>
                    </div>
                    <div v-else>
                        <div class="text-xs text-muted-foreground mb-2">Mã giảm giá đã áp dụng</div>
                        <Input disabled :placeholder="getDiscountedAmountText(props.order?.total, props.order?.final_total)"
                            class="w-full" />
                    </div>
                </div>

                <!-- actions -->
                <div class="flex gap-3 bg-white fixed bottom-[3vh] w-[90%] md:w-[45%] lg:w-[55%] justify-self-center">
                    <button v-if="(props.mode === 'current')" @click="goToChat(props.order?.thread_id??null)"
                        :class="(props.order?.status == OrderStatus.CONFIRMED || props.order?.status == OrderStatus.IN_JOB) ? 'bg-primary-500 cursor-pointer' : 'bg-gray-500 cursor-not-allowed'"
                        class="h-10 rounded-md text-white flex-1">Chat ngay</button>

                    <button v-if="(props.mode === 'current')" @click="emit('cancel-order')"
                        class="h-10 cursor-pointer rounded-md border border-destructive text-destructive bg-transparent flex-1 hover:bg-destructive/5">
                        Hủy đơn
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
