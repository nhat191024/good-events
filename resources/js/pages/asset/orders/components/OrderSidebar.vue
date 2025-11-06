<script setup lang="ts">
import { computed } from 'vue';

import OrderListItem from './OrderListItem.vue';

import type { AssetOrder } from '../types';

const props = withDefaults(defineProps<{
    orders: AssetOrder[]
    selectedId: number | null
    loading?: boolean
    hasMore?: boolean
    initializing?: boolean
}>(), {
    orders: () => [],
    selectedId: null,
    loading: false,
    hasMore: false,
    initializing: false,
});

const emit = defineEmits<{
    (e: 'select', order: AssetOrder): void
    (e: 'load-more'): void
}>();

const isEmpty = computed(() => !props.initializing && props.orders.length === 0);
const skeletonItems = computed(() => Array.from({ length: 3 }, (_, index) => index));
</script>

<template>
    <aside class="flex h-full flex-col gap-4">
        <header class="flex items-center justify-between">
            <h2 class="font-lexend text-lg font-semibold text-gray-900">
                Đơn hàng đã mua
            </h2>
            <span class="rounded-full bg-primary-50 px-3 py-1 text-xs font-medium text-primary-700">
                {{ orders.length }}
            </span>
        </header>

        <div class="flex-1 space-y-3 overflow-y-auto pr-1">
            <template v-if="initializing">
                <div
                    v-for="item in skeletonItems"
                    :key="item"
                    class="animate-pulse rounded-xl border border-dashed border-primary-200 bg-primary-50/50 p-4"
                >
                    <div class="h-4 w-3/4 rounded bg-primary-200/80"></div>
                    <div class="mt-3 h-3 w-1/2 rounded bg-primary-200/70"></div>
                    <div class="mt-4 flex items-center justify-between">
                        <div class="h-4 w-20 rounded bg-primary-200/90"></div>
                        <div class="h-3 w-16 rounded bg-primary-200/60"></div>
                    </div>
                </div>
            </template>

            <p v-else-if="isEmpty" class="rounded-xl border border-dashed border-gray-200 bg-gray-50 p-6 text-center text-sm text-gray-500">
                Bạn chưa mua tài liệu nào.
            </p>

            <template v-else>
                <OrderListItem
                    v-for="order in orders"
                    :key="order.id"
                    :order="order"
                    :selected="order.id === selectedId"
                    @click="emit('select', order)"
                />
            </template>
        </div>

        <button
            v-if="hasMore"
            type="button"
            class="inline-flex h-10 items-center justify-center rounded-lg border border-primary-200 bg-white px-4 text-sm font-medium text-primary-700 hover:bg-primary-50 disabled:pointer-events-none disabled:opacity-60"
            :disabled="loading"
            @click="emit('load-more')"
        >
            <span v-if="loading">Đang tải...</span>
            <span v-else>Xem thêm đơn hàng</span>
        </button>
    </aside>
</template>
