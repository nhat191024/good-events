<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { debounce } from '../helper';
import { ClientOrderHistory } from '../types';
import OrderHistoryCard from './OrderHistoryCard.vue'
import { RefreshCw } from 'lucide-vue-next';

const props = withDefaults(defineProps<{
    orders: ClientOrderHistory[]
    loading?: boolean
    selectedId?: number | null
    // searchKeyword?: string
}>(), {
    // searchKeyword: '',
    loading: false,
    selectedId: null,
})

const emit = defineEmits<{ (e: 'select', order: any): void; (e: 'reload'): void, (e: 'load-more'): void }>()

const sentinel = ref<HTMLElement | null>(null)
let observer: IntersectionObserver | null = null

onMounted(() => {
    // tạo observer một lần
    observer = new IntersectionObserver((entries) => {
        const entry = entries[0]
        // khi sentinel vào viewport và đang ko loading → bắn 'load-more'
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
        <!-- khi có orders -->
        <template v-if="props.orders.length > 0">
            <div v-for="o in props.orders" :key="o.id" @click="emit('select', o)">
                <OrderHistoryCard v-bind="o" :selected="o.id === props.selectedId" />
            </div>
        </template>

        <!-- khi không có orders -->
        <div v-else class="flex flex-col items-center justify-center py-12 text-muted-foreground">
            <RefreshCw class="h-8 w-8 mb-3 animate-spin-slow" />
            <p class="text-sm mb-2">Hiện chưa có đơn hàng nào</p>
            <button class="px-3 h-9 rounded-md border border-border text-sm hover:underline active:text-black"
                @click="emit('reload')">
                thử tải lại
            </button>
        </div>

        <div ref="sentinel" class="h-8"></div>
        <div v-if="props.loading" class="pb-4 text-center text-xs text-muted-foreground">
            đang tải thêm...
        </div>
    </div>
</template>
