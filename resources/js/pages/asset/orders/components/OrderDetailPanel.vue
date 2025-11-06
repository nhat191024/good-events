<script setup lang="ts">
import { computed } from 'vue';

import { formatDate, formatPrice } from '@/lib/helper';

import type { AssetOrder } from '../types';

const props = withDefaults(defineProps<{
    order: AssetOrder | null
    loading?: boolean
}>(), {
    order: null,
    loading: false,
});

const emit = defineEmits<{
    (e: 'repay', order: AssetOrder): void
}>();

const product = computed(() => props.order?.file_product ?? null);

const createdAt = computed(() => props.order ? formatDate(props.order.created_at) : null);
const updatedAt = computed(() => props.order ? formatDate(props.order.updated_at) : null);

const totalAmount = computed(() => props.order ? `${formatPrice(props.order.total)} đ` : null);
const finalAmount = computed(() => {
    if (!props.order) return null;
    const value = props.order.final_total ?? props.order.total;
    return `${formatPrice(value)} đ`;
});
const hasDiscount = computed(() => {
    if (!props.order) return false;
    if (props.order.final_total === null) return false;
    return props.order.final_total !== props.order.total;
});

const descriptionPreview = computed(() => {
    if (!product.value?.description) return null;
    const plain = product.value.description.replace(/<[^>]+>/g, ' ').replace(/\s+/g, ' ').trim();
    return plain || null;
});

function statusClass(status: string): string {
    switch (status) {
        case 'pending':
            return 'bg-amber-100 text-amber-700 ring-amber-200';
        case 'paid':
            return 'bg-emerald-100 text-emerald-700 ring-emerald-200';
        case 'cancelled':
            return 'bg-rose-100 text-rose-700 ring-rose-200';
        default:
            return 'bg-gray-100 text-gray-600 ring-gray-200';
    }
}

const detailStatusClass = computed(() => statusClass(String(props.order?.status ?? '').toLowerCase()));

function triggerRepay() {
    if (props.order && props.order.can_repay) {
        emit('repay', props.order);
    }
}
</script>

<template>
    <section class="flex h-full flex-col rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
        <header class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-4">
            <div>
                <h2 class="font-lexend text-xl font-semibold text-gray-900">
                    Chi tiết đơn tài liệu
                </h2>
                <p class="text-sm text-gray-500">
                    Xem thông tin tài liệu đã mua và trạng thái thanh toán.
                </p>
            </div>
            <span
                v-if="order"
                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-medium ring-1 ring-inset"
                :class="detailStatusClass"
            >
                {{ order.status_label }}
            </span>
        </header>

        <div v-if="loading && !order" class="mt-8 grid gap-4 md:grid-cols-[160px_minmax(0,1fr)]">
            <div class="h-40 w-full animate-pulse rounded-2xl bg-gray-100 md:h-44"></div>
            <div class="space-y-4">
                <div class="h-6 w-2/3 animate-pulse rounded bg-gray-100"></div>
                <div class="h-4 w-full animate-pulse rounded bg-gray-100"></div>
                <div class="h-4 w-5/6 animate-pulse rounded bg-gray-100"></div>
                <div class="h-4 w-1/2 animate-pulse rounded bg-gray-100"></div>
            </div>
        </div>

        <div v-else-if="order" class="mt-6 flex flex-1 flex-col gap-6">
            <div class="grid gap-4 md:grid-cols-[minmax(0,200px)_minmax(0,1fr)]">
                <div class="relative overflow-hidden rounded-2xl bg-gray-50">
                    <template v-if="product?.thumbnail">
                        <img
                            :src="product.thumbnail"
                            :alt="product.name"
                            class="h-full w-full object-cover"
                            loading="lazy"
                        >
                    </template>
                    <div v-else class="flex h-full min-h-[160px] items-center justify-center bg-gradient-to-br from-primary-100 via-primary-200 to-primary-300 text-sm font-medium text-primary-900">
                        {{ product?.name || `Đơn #${order.id}` }}
                    </div>
                </div>

                <div class="flex flex-col gap-4">
                    <div>
                        <h3 class="font-lexend text-lg font-semibold text-gray-900">
                            {{ product?.name || `Đơn #${order.id}` }}
                        </h3>
                        <p v-if="product?.category?.name" class="text-sm text-gray-500">
                            Danh mục: {{ product.category.name }}
                        </p>
                    </div>

                    <p v-if="descriptionPreview" class="text-sm leading-relaxed text-gray-600 line-clamp-4">
                        {{ descriptionPreview }}
                    </p>

                    <div class="grid gap-3 rounded-2xl bg-gray-50 p-4 text-sm text-gray-700 sm:grid-cols-2">
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wide text-gray-500">Ngày tạo</span>
                            <span class="font-medium">{{ createdAt }}</span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <span class="text-xs uppercase tracking-wide text-gray-500">Cập nhật</span>
                            <span class="font-medium">{{ updatedAt }}</span>
                        </div>
                        <div class="flex flex-col gap-1 sm:col-span-2">
                            <span class="text-xs uppercase tracking-wide text-gray-500">Giá tài liệu</span>
                            <span class="font-semibold text-primary-700">{{ totalAmount }}</span>
                            <span v-if="hasDiscount" class="text-xs text-emerald-600">
                                Thành tiền sau khi điều chỉnh: {{ finalAmount }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-auto flex flex-wrap items-center justify-between gap-3 border-t border-gray-100 pt-4">
                <div class="text-sm text-gray-500">
                    Mã đơn: <span class="font-medium text-gray-700">#{{ order.id }}</span>
                </div>

                <div class="flex flex-wrap items-center gap-3">
                    <button
                        type="button"
                        class="inline-flex h-11 min-w-[140px] items-center justify-center rounded-xl border border-primary-200 bg-white px-4 text-sm font-medium text-primary-700 shadow-sm hover:bg-primary-50 disabled:pointer-events-none disabled:opacity-60"
                        :disabled="!order.can_download"
                    >
                        Tải tài liệu
                    </button>
                    <button
                        v-if="order.can_repay"
                        type="button"
                        class="inline-flex h-11 min-w-[140px] items-center justify-center rounded-xl bg-primary-600 px-4 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
                        @click="triggerRepay"
                    >
                        Thanh toán lại
                    </button>
                </div>
            </div>
        </div>

        <div v-else class="mt-6 flex flex-1 flex-col items-center justify-center rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-12 text-center">
            <h3 class="font-lexend text-lg font-semibold text-gray-700">
                Chọn một đơn hàng bên trái
            </h3>
            <p class="mt-2 max-w-md text-sm text-gray-500">
                Danh sách bên trái hiển thị các đơn tài liệu bạn đã mua. Hãy chọn một đơn để xem chi tiết và thao tác thanh toán.
            </p>
        </div>
    </section>
</template>
