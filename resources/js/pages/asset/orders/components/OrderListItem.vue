<script setup lang="ts">
import { computed } from 'vue';

import { formatDate, formatPrice } from '@/lib/helper';
import { cn } from '@/lib/utils';

import type { AssetOrder } from '../types';

const props = withDefaults(defineProps<{
    order: AssetOrder
    selected?: boolean
}>(), {
    selected: false,
});

const productName = computed(() => props.order.file_product?.name ?? `Đơn #${props.order.id}`);
const categoryName = computed(() => props.order.file_product?.category?.name ?? null);
const createdAt = computed(() => formatDate(props.order.created_at));
const amountText = computed(() => `${formatPrice(props.order.final_total ?? props.order.total)} đ`);

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

const statusBadgeClass = computed(() => statusClass(String(props.order.status).toLowerCase()));
</script>

<template>
    <button
        type="button"
        :class="cn(
            'w-full rounded-xl border p-4 text-left transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400',
            selected
                ? 'border-primary-400 bg-primary-50 shadow-sm'
                : 'border-transparent hover:border-primary-200 hover:bg-primary-50/50'
        )"
    >
        <div class="flex items-start justify-between gap-3">
            <div class="flex flex-1 flex-col gap-1">
                <h3 class="font-semibold text-sm text-gray-900 md:text-base">
                    {{ productName }}
                </h3>
                <p v-if="categoryName" class="text-xs text-gray-500 md:text-sm">
                    Danh mục: {{ categoryName }}
                </p>
            </div>
            <span
                class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset"
                :class="statusBadgeClass"
            >
                {{ order.status_label }}
            </span>
        </div>

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2 text-sm">
            <span class="font-semibold text-primary-700">
                {{ amountText }}
            </span>
            <span class="text-xs text-gray-500 md:text-sm">
                {{ createdAt }}
            </span>
        </div>
    </button>
</template>
