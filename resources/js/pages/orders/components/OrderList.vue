<script setup lang="ts">
// note: danh sách đơn hiện tại + lọc theo keyword + emit khi click item
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import OrderCard from './OrderCard.vue'
import { createSearchFilter } from '../../../lib/search-filter'
import { ClientOrder, OrderStatus } from '../types';
import { RefreshCw } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    orders: ClientOrder[]
    loading?: boolean
    selectedId?: number | null
}>(), {
    loading: false,
    selectedId: null,
})

const emit = defineEmits<{ (e: 'select', order: any): void, (e: 'load-more'): void }>()

const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

onMounted(() => {
    observer = new IntersectionObserver((entries) => {
        const entry = entries[0]
        if (entry.isIntersecting && !props.loading && props.orders.length > 0) {
            emit('load-more')
        }
    }, { root: null, rootMargin: '0px', threshold: 0.1 })

    if (sentinel.value) observer.observe(sentinel.value)
})

onBeforeUnmount(() => {
    observer?.disconnect()
})
</script>

<template>
    <div class="space-y-4">
        <div v-for="o in orders" :key="o.id" @click="emit('select', o)">
            <OrderCard v-bind="o" :selected="o.id === props.selectedId" />
        </div>

        <div v-if="orders.length === 0" class="flex flex-col items-center justify-center py-12 text-muted-foreground">
            <RefreshCw class="h-8 w-8 mb-3 animate-spin-slow" />
            <p class="text-sm mb-2">Hiện chưa có đơn hàng nào</p>
        </div>
        <!-- sentinel + loading -->
        <div ref="sentinel" class="h-8"></div>
        <div v-if="props.loading" class="pb-4 text-center text-xs text-muted-foreground">
            đang tải thêm...
        </div>
    </div>
</template>
