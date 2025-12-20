<script setup lang="ts">

import { CalendarDays, Clock, MapPin, Award, StickyNote, Handshake, BadgeDollarSign, ArrowUpRightFromSquare, Star } from 'lucide-vue-next'
import { ClientOrder, ClientOrderDetail, OrderStatus } from '../types';
import { computed, onMounted, ref, watch } from 'vue';
import { formatDate, formatPrice, formatTimeRange } from '@/lib/helper';
import { Input } from '@/components/ui/input';
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { router } from '@inertiajs/core';
import { statusBadge } from '../helper';
import Button from '@/components/ui/button/Button.vue';
import { useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { SwitchRoot, SwitchThumb } from 'reka-ui';

const props = withDefaults(defineProps<{
    mode?: 'current' | 'history'
    order?: ClientOrder | null
    bookedPartner?: ClientOrderDetail | null
    modelValue?: string
    applyVoucher?: boolean
}>(), {
    mode: 'current',
    order: undefined,
    modelValue: '',
    applyVoucher: true,
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
    (e: 'update:applyVoucher', value: boolean): void
}>()

const applyVoucher = computed({
    get: () => props.applyVoucher ?? true,
    set: (value: boolean) => emit('update:applyVoucher', value),
})

function goToChat(thread_id: number | null, canAccess: boolean) {
    if (!thread_id || canAccess) return
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

type VoucherDetails = {
    code: string
    discount_percent?: number | null
    max_discount_amount?: number | null
    min_order_amount?: number | null
    usage_limit?: number | null
    times_used?: number | null
    is_unlimited?: boolean
    starts_at?: string | null
    expires_at?: string | null
}

const voucherCheckResult = ref<{ status: boolean; message: string; details: VoucherDetails | null } | null>(null)
const showVoucherDialog = ref(false)

const voucherStatusBadge = computed(() => {
    if (!voucherCheckResult.value) {
        return { text: 'Chưa kiểm tra', cls: 'bg-gray-100 text-gray-700' }
    }

    if (voucherCheckResult.value.status) {
        return { text: 'Khả dụng', cls: 'bg-emerald-100 text-emerald-700' }
    }

    if (!voucherCheckResult.value.details) {
        return { text: 'Không hợp lệ', cls: 'bg-red-100 text-red-700' }
    }

    return { text: 'Cần lưu ý', cls: 'bg-amber-100 text-amber-700' }
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

        voucherCheckResult.value = {
            status: data?.status ?? false,
            message: data?.message ?? 'Không thể kiểm tra mã giảm giá, thử lại sau.',
            details: buildVoucherDetails(data?.details),
        }
    } catch (error) {
        voucherCheckResult.value = {
            status: false,
            message: 'Chưa thể lấy thông tin mã giảm giá, vui lòng thử lại sau!',
            details: buildVoucherDetails(),
        }
    } finally {
        showVoucherDialog.value = true
        form.processing = false
    }
}

function buildVoucherDetails(details?: Partial<VoucherDetails> | null): VoucherDetails | null {
    if (!details) return null

    return {
        code: details?.code ?? form.voucher_input,
        discount_percent: details?.discount_percent ?? null,
        max_discount_amount: details?.max_discount_amount ?? null,
        min_order_amount: details?.min_order_amount ?? null,
        usage_limit: details?.usage_limit ?? null,
        times_used: details?.times_used ?? null,
        is_unlimited: details?.is_unlimited ?? false,
        starts_at: details?.starts_at ?? null,
        expires_at: details?.expires_at ?? null,
    }
}

function closeVoucherDialog() {
    showVoucherDialog.value = false
}

function formatDateSafe(val?: string | null) {
    if (!val) return ''
    return formatDate(val)
}

function loadVoucherFromStorage() {
    const saved = localStorage.getItem(storageKey.value)
    form.voucher_input = saved ?? ''
}

function getDiscountedAmountText(total: number | null | undefined, finalTotal: number | null | undefined) {
    if (total == null || finalTotal == null) return 'Không'
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
        <div class="px-3 md:px-6 pt-3 md:pt-6 pb-12">
            <div class="text-center mb-3 md:mb-6">
                <div class="inline-flex items-center justify-center w-16 h-16 bg-orange-100 rounded-full mb-3">
                    <ImageWithLoader :src="getImg(props.order?.category.image)" alt="Traditional Vietnamese scholar"
                        class="w-[95%] h-full rounded-full" img-class="w-[95%] h-full object-cover rounded-full border"
                        loading="lazy" />
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
                    <div
                        class="flex items-center gap-3 p-2 md:p-4 bg-amber-50 border border-amber-200 rounded-xl shadow-sm">
                        <Star class="h-5 w-5 text-amber-500" />
                        <div>
                            <div class="text-xs text-amber-700 font-semibold">
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
                            <div class="text-sm md:text-md font-semibold text-amber-900">
                                {{ props.order?.review?.comment ?? 'Không' }}
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
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <div class="text-xs text-muted-foreground">
                                    Mã giảm giá (Áp dụng vào giá chốt của ứng viên bạn chọn)
                                </div>
                                <div class="flex gap-2 items-center">
                                    <span class="text-xs text-muted-foreground select-none">Áp dụng mã</span>
                                    <SwitchRoot id="apply-voucher" v-model="applyVoucher"
                                        class="w-[36px] h-[22px] shadow-sm flex data-[state=unchecked]:bg-stone-300 data-[state=checked]:bg-stone-800 dark:data-[state=unchecked]:bg-stone-800 dark:data-[state=checked]:bg-stone-700 border border-stone-300 data-[state=checked]:border-stone-700  dark:border-stone-700 rounded-full relative transition-[background] focus-within:outline-none focus-within:shadow-[0_0_0_1px] focus-within:border-stone-800 focus-within:shadow-stone-800">
                                        <SwitchThumb
                                            class="w-4 h-4 my-auto bg-white text-xs flex items-center justify-center shadow-xl rounded-full transition-transform translate-x-0.5 will-change-transform data-[state=checked]:translate-x-full" />
                                    </SwitchRoot>
                                </div>
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
                        <div class="grid gap-2 sm:grid-cols-2">
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700">
                                Mã áp dụng: {{ props.order?.voucher?.code ?? 'Không áp dụng' }}
                            </div>
                            <div class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50 text-gray-700">
                                {{ getDiscountedAmountText(props.order?.total, props.order?.final_total) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- actions -->
                <div class="flex gap-3 bg-white fixed bottom-[3vh] w-[90%] md:w-[45%] lg:w-[55%] justify-self-center">
                    <button v-if="(props.mode === 'current')"
                        @click="goToChat(props.order?.thread_id ?? null, !(props.order?.status == OrderStatus.CONFIRMED || props.order?.status == OrderStatus.IN_JOB))"
                        :class="(props.order?.status == OrderStatus.CONFIRMED || props.order?.status == OrderStatus.IN_JOB) ? 'bg-primary-500 cursor-pointer' : 'bg-gray-500 cursor-not-allowed'"
                        class="h-10 rounded-md text-white flex-1">Chat ngay</button>

                    <button
                        v-if="props.mode === 'current' && !(props.order?.status == OrderStatus.CONFIRMED || props.order?.status == OrderStatus.IN_JOB)"
                        @click="emit('cancel-order')"
                        class="h-10 cursor-pointer rounded-md border border-destructive text-destructive bg-transparent flex-1 hover:bg-destructive/5">
                        Hủy đơn
                    </button>
                </div>
            </div>
        </div>
        <div v-if="showVoucherDialog" class="fixed inset-0 z-[70] grid place-items-center bg-black/40 p-4"
            @click.self="closeVoucherDialog">
            <div class="w-full max-w-2xl rounded-xl bg-card text-card-foreground border border-border shadow-lg">
                <div class="p-4 border-b border-border flex items-start justify-between gap-3">
                    <div>
                        <div class="text-xs text-muted-foreground mb-1">Kết quả kiểm tra mã</div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-lg font-semibold">
                                {{ voucherCheckResult?.details?.code ?? form.voucher_input }}
                            </span>
                            <span :class="['px-2 py-1 rounded-full text-xs font-semibold', voucherStatusBadge.cls]">
                                {{ voucherStatusBadge.text }}
                            </span>
                        </div>
                        <p class="text-sm text-muted-foreground mt-1 leading-relaxed">
                            {{ voucherCheckResult?.message ?? 'Kiểm tra mã giảm giá để xem chi tiết.' }}
                        </p>
                    </div>
                    <!-- Action close button -->
                    <button class="text-muted-foreground hover:text-foreground text-xl leading-none" type="button"
                        @click="closeVoucherDialog">
                        ×
                    </button>
                </div>

                <div class="p-4 space-y-4">
                    <div v-if="voucherCheckResult?.details" class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <div class="p-3 rounded-lg border border-border/70 bg-muted/20">
                            <div class="text-xs text-muted-foreground mb-1">Mức giảm</div>
                            <div class="text-base font-semibold">
                                {{ voucherCheckResult?.details?.discount_percent
                                    ? voucherCheckResult?.details?.discount_percent + '% trên giá chốt'
                                    : 'Không giới hạn phần trăm' }}
                            </div>
                            <div class="text-xs text-muted-foreground">
                                Tối đa: {{ voucherCheckResult?.details?.max_discount_amount
                                    ? formatPrice(voucherCheckResult?.details?.max_discount_amount) + 'đ'
                                    : 'Không giới hạn' }}
                            </div>
                        </div>

                        <div class="p-3 rounded-lg border border-border/70 bg-muted/20">
                            <div class="text-xs text-muted-foreground mb-1">Điều kiện đơn tối thiểu</div>
                            <div class="text-base font-semibold">
                                {{ voucherCheckResult?.details?.min_order_amount
                                    ? formatPrice(voucherCheckResult?.details?.min_order_amount) + 'đ'
                                    : 'Không yêu cầu' }}
                            </div>
                            <div class="text-xs text-muted-foreground">Mã sẽ áp dụng khi bạn chốt giá với đối tác.</div>
                        </div>

                        <div class="p-3 rounded-lg border border-border/70 bg-muted/20">
                            <div class="text-xs text-muted-foreground mb-1">Thời gian hiệu lực</div>
                            <div class="text-sm font-semibold">
                                {{ voucherCheckResult?.details?.starts_at
                                    ? 'Bắt đầu: ' + formatDateSafe(voucherCheckResult?.details?.starts_at)
                                    : 'Hiệu lực ngay' }}
                            </div>
                            <div class="text-sm font-semibold">
                                {{ voucherCheckResult?.details?.expires_at
                                    ? 'Hết hạn: ' + formatDateSafe(voucherCheckResult?.details?.expires_at)
                                    : 'Không có hạn' }}
                            </div>
                        </div>

                        <div class="p-3 rounded-lg border border-border/70 bg-muted/20">
                            <div class="text-xs text-muted-foreground mb-1">Lượt sử dụng</div>
                            <div class="text-base font-semibold">
                                {{ voucherCheckResult?.details?.is_unlimited
                                    ? 'Không giới hạn lượt dùng'
                                    : `${voucherCheckResult?.details?.times_used ??
                                    0}/${voucherCheckResult?.details?.usage_limit ??
                                    '∞'}` }}
                            </div>
                            <div class="text-xs text-muted-foreground">Giữ nguyên mã khi bấm "Chọn đối tác".</div>
                        </div>
                    </div>

                    <div v-else
                        class="p-3 rounded-lg border border-border/70 bg-muted/10 text-sm text-muted-foreground">
                        Không tìm thấy thông tin chi tiết cho mã này. Vui lòng kiểm tra lại mã hoặc thử lại sau.
                    </div>

                    <div class="text-xs text-muted-foreground">
                        Mã đã được lưu tạm thời, chỉ cần giữ nguyên trường mã khi bấm "Chọn đối tác" để áp dụng.
                    </div>
                </div>

                <div class="p-4 border-t border-border flex gap-2">
                    <button class="flex-1 h-10 rounded-md border border-border bg-transparent" type="button"
                        @click="closeVoucherDialog">
                        Đóng
                    </button>
                    <button class="flex-1 h-10 rounded-md bg-primary-700 text-white" type="button"
                        @click="closeVoucherDialog">
                        Lưu mã
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
