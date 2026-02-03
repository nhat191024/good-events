<script setup lang="ts">

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Package, History, SlidersHorizontal, Filter, ChevronDown, Calendar, Search } from 'lucide-vue-next'
import { Motion } from 'motion-v'
import OrderList from '../components/OrderList.vue'
import OrderHistoryList from '../components/OrderHistoryList.vue'

import { createSearchFilter } from '../../../lib/search-filter'
import { ClientOrder, ClientOrderHistory, OrderStatus } from '../types'
import { debounce } from '../helper'
import ReloadButton from '../components/ReloadButton.vue'

const isReloading = ref(false)

const props = defineProps<{
    orderList: ClientOrder[]
    orderHistory: ClientOrderHistory[]
    historyLoading?: boolean
    orderLoading?: boolean
    selectedOrderId?: number | null
    selectedMode?: 'current' | 'history'
}>()

const orderHistory = computed(() => props.orderHistory)

const activeTab = defineModel<'current' | 'history'>('activeTab', { default: 'current' })

const showAdvancedFilter = ref(false)
const kw = ref('')
const sortBy = ref<'newest' | 'oldest' | 'most-applicants' | 'highest-budget' | 'lowest-budget'>('newest')
const sortByHistory = ref<'newest' | 'oldest' | 'highest-budget' | 'lowest-budget'>('newest')

const statusFilter = ref<string[]>([
    OrderStatus.CONFIRMED,
    OrderStatus.PENDING,
    OrderStatus.CANCELLED,
    OrderStatus.COMPLETED,
    OrderStatus.EXPIRED,
    OrderStatus.IN_JOB,
])
const locationsFilter = ref<string[]>([])

//! change the status group display priority here 
const STATUS_ORDER: Array<ClientOrder['status']> = [
    OrderStatus.PENDING,
    OrderStatus.CONFIRMED,
    OrderStatus.IN_JOB,
    OrderStatus.CANCELLED,
    OrderStatus.COMPLETED,
    OrderStatus.EXPIRED,
]

const STATUS_RANK: Record<string, number> = STATUS_ORDER.reduce(
    (acc, s, i) => ((acc[s as string] = i), acc),
    {} as Record<string, number>,
)

function parseOrderDateTime(order: Pick<ClientOrder, 'date' | 'start_time'>): number | null {
    const datePart = order.date?.trim()
    if (!datePart) return null

    const timePart = order.start_time?.trim()
    const normalizedTime = timePart
        ? /^\d{2}:\d{2}$/.test(timePart)
            ? `${timePart}:00`
            : timePart
        : '00:00:00'

    const combined = `${datePart}T${normalizedTime}`
    const parsed = Date.parse(combined)
    if (!Number.isNaN(parsed)) return parsed

    const fallback = Date.parse(datePart)
    return Number.isNaN(fallback) ? null : fallback
}

function baseCompare(a: ClientOrder, b: ClientOrder) {
    if (sortBy.value === 'newest' || sortBy.value === 'oldest') {
        const aTime = parseOrderDateTime(a)
        const bTime = parseOrderDateTime(b)

        if (aTime === null && bTime === null) return 0
        if (aTime === null) return 1
        if (bTime === null) return -1

        return sortBy.value === 'newest' ? aTime - bTime : bTime - aTime
    }

    if (sortBy.value === 'most-applicants') {
        const ca = a.partners?.count ?? 0
        const cb = b.partners?.count ?? 0
        return cb - ca
    }

    if (sortBy.value === 'highest-budget') return (b.final_total ?? 0) - (a.final_total ?? 0)
    if (sortBy.value === 'lowest-budget') return (a.final_total ?? 0) - (b.final_total ?? 0)

    return 0
}

function sortOrders(list: ClientOrder[]) {
    const klone = [...list]
    return klone.sort((a, b) => {
        const ra = STATUS_RANK[a.status] ?? 99
        const rb = STATUS_RANK[b.status] ?? 99
        if (ra !== rb) return ra - rb
        return baseCompare(a, b)
    })
}

function sortHistoryOrders(list: ClientOrderHistory[]) {
    const klone = [...list]
    switch (sortByHistory.value) {
        case 'newest':
            return klone.sort((a, b) => Date.parse(b.updated_at) - Date.parse(a.updated_at))
        case 'oldest':
            return klone.sort((a, b) => Date.parse(a.updated_at) - Date.parse(b.updated_at))
        case 'highest-budget':
            return klone.sort((a, b) => (b.final_total || 0) - (a.final_total || 0))
        case 'lowest-budget':
            return klone.sort((a, b) => (a.final_total || 0) - (b.final_total || 0))
        default:
            return klone
    }
}

const visibleOrders = computed(() => {
    let list = [...props.orderList]

    try {
        const fn = createSearchFilter(
            ['code', 'note', 'status', 'address', 'start_time', 'end_time', 'final_total', 'category.name', 'category.parent.name', 'event.name'],
            kw.value,
        )
        list = list.filter(fn)
    } catch { }

    if (statusFilter.value.length) {
        list = list.filter((o) => statusFilter.value.includes(o.status))
    }


    if (locationsFilter.value.length) {
        list = list.filter((o) => locationsFilter.value.includes(o.address))
    }

    return sortOrders(list)
})

const visibleHistoryOrders = computed(() => {
    let list = [...props.orderHistory]

    try {
        const fn = createSearchFilter(
            ['code', 'note', 'status', 'address', 'start_time', 'end_time', 'final_total', 'category.name', 'category.parent.name', 'event.name'],
            kw.value,
        )
        list = list.filter(fn)
    } catch { }

    if (statusFilter.value.length) {
        list = list.filter((o) => statusFilter.value.includes(o.status))
    }

    if (locationsFilter.value.length) {
        list = list.filter((o) => locationsFilter.value.includes(o.address))
    }

    return sortHistoryOrders(list)
})

function onDocClick(e: MouseEvent) {
    const el = e.target as HTMLElement
    if (!el.closest?.('#orders-adv-filter')) showAdvancedFilter.value = false
}

const reloadHistory = debounce(() => {
    isReloading.value = true
    emit('reload')
    setTimeout(() => {
        isReloading.value = false
    }, 10000)
}, 5000)

const reloadOrders = debounce(() => {
    isReloading.value = true
    emit('reload-orders')
    setTimeout(() => {
        isReloading.value = false
    }, 10000)
}, 5000)

const loadMoreHistory = () => {
    emit('load-history-more')
}

const loadMoreOrders = () => {
    emit('load-orders-more')
}

onMounted(() => document.addEventListener('click', onDocClick))

onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

const emit = defineEmits<{
    (e: 'select', payload: { order: any; mode: 'current' | 'history' }): void
    (e: 'load-history'): void
    (e: 'reload'): void
    (e: 'reload-orders'): void
    (e: 'load-history-more'): void
    (e: 'load-orders-more'): void
}>()

const selectedCurrentId = computed(() =>
    props.selectedMode === 'current' ? props.selectedOrderId ?? null : null
)

const selectedHistoryId = computed(() =>
    props.selectedMode === 'history' ? props.selectedOrderId ?? null : null
)
</script>

<template>
    <div
        class="w-full md:w-96 md:flex-shrink-0 bg-sidebar border-r border-sidebar-border overflow-y-auto scrollbar-hide h-full flex flex-col relative">
        <div class="px-1 md:px-4 flex-1">
            <!-- <div
                class="fixed top-0 z-10 w-1/3 h-20 bg-sidebar/95 backdrop-blur-sm -mx-2 px-2 pb-2 space-y-4 border-b border-gray-100 m-0">
            </div> -->
            <!-- tabs control -->
            <div class="sticky top-0 z-20 bg-sidebar/80 backdrop-blur-md py-0">
                <div class="flex p-1 bg-gray-100/80 rounded-xl gap-1">
                    <button type="button"
                        class="relative flex-1 h-10 rounded-lg flex items-center justify-center gap-2 text-sm font-medium transition-all duration-200"
                        :class="activeTab === 'current' ? 'text-white' : 'text-gray-600 hover:bg-gray-200/50'"
                        @click="activeTab = 'current'">
                        <div v-if="activeTab === 'current'"
                            class="absolute inset-0 bg-primary-700 rounded-lg shadow-sm"></div>
                        <Package class="h-4 w-4 relative z-10" />
                        <span class="relative z-10">Đơn hiện tại</span>
                    </button>
                    <button type="button"
                        class="relative flex-1 h-10 rounded-lg flex items-center justify-center gap-2 text-sm font-medium transition-all duration-200"
                        :class="activeTab === 'history' ? 'text-white' : 'text-gray-600 hover:bg-gray-200/50'"
                        @click="activeTab = 'history'">
                        <div v-if="activeTab === 'history'"
                            class="absolute inset-0 bg-primary-700 rounded-lg shadow-sm"></div>
                        <History class="h-4 w-4 relative z-10" />
                        <span class="relative z-10">Lịch sử</span>
                    </button>
                </div>
            </div>

            <Motion :key="activeTab" :initial="{ opacity: 0, y: 10 }" :animate="{ opacity: 1, y: 0 }"
                :transition="{ duration: 0.3 }">

                <!-- CURRENT TAB -->
                <div v-if="activeTab === 'current'" class="space-y-4">
                    <div
                        class="sticky top-10 z-10 bg-sidebar/95 backdrop-blur-sm -mx-2 px-2 py-2 space-y-4 border-b border-gray-100 m-0">
                        <div class="flex items-center justify-between px-1">
                            <div>
                                <h2 class="text-xl font-lexend font-bold text-gray-900">Đơn hàng</h2>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Hiện tại của
                                    bạn</p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span
                                    class="bg-primary-50 text-primary-700 text-xs font-bold px-2 py-0.5 rounded-full border border-primary-100">
                                    {{ visibleOrders.length }} đơn
                                </span>
                                <ReloadButton :is-reloading="isReloading" @reload="reloadOrders()" />
                            </div>
                        </div>

                        <!-- filters -->
                        <div class="space-y-3">
                            <!-- search keyword -->
                            <div class="relative group">
                                <Search
                                    class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-primary-600 transition-colors" />
                                <input v-model="kw" placeholder="Tìm kiếm đơn hàng..."
                                    class="w-full h-10 rounded-xl bg-gray-50/50 border border-gray-200 pl-10 pr-4 text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                                    type="text" />
                            </div>

                            <!-- sort & filter row -->
                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <select v-model="sortBy"
                                        class="w-full h-9 rounded-lg bg-gray-50/50 border border-gray-200 px-3 text-xs font-medium cursor-pointer hover:bg-gray-100 transition-colors appearance-none outline-none">
                                        <option value="newest">Đơn sắp tới</option>
                                        <option value="oldest">Đơn muộn nhất</option>
                                        <option v-if="activeTab == 'current'" value="most-applicants">Nhiều ứng viên
                                            nhất
                                        </option>
                                        <option value="highest-budget">Ngân sách cao nhất</option>
                                        <option value="lowest-budget">Ngân sách thấp nhất</option>
                                    </select>
                                    <ChevronDown
                                        class="absolute right-2 top-1/2 -translate-y-1/2 h-3 w-3 text-gray-400 pointer-events-none" />
                                </div>
                            </div>

                            <!-- filter chips -->
                            <div class="flex flex-wrap gap-2 px-1">
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" class="hidden" :value="OrderStatus.PENDING"
                                        v-model="statusFilter" />
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-bold transition-all border',
                                        statusFilter.includes(OrderStatus.PENDING)
                                            ? 'bg-amber-100 text-amber-700 border-amber-200 shadow-sm ring-2 ring-amber-800/70'
                                            : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400'
                                    ]">Đang chờ</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" class="hidden" :value="OrderStatus.CONFIRMED"
                                        v-model="statusFilter" />
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-bold transition-all border',
                                        statusFilter.includes(OrderStatus.CONFIRMED)
                                            ? 'bg-blue-100 text-blue-700 border-blue-200 shadow-sm ring-2 ring-blue-800/70'
                                            : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400'
                                    ]">Đã chốt</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" class="hidden" :value="OrderStatus.IN_JOB"
                                        v-model="statusFilter" />
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-bold transition-all border',
                                        statusFilter.includes(OrderStatus.IN_JOB)
                                            ? 'bg-green-100 text-green-700 border-green-200 shadow-sm ring-2 ring-green-800/70'
                                            : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400'
                                    ]">Đã đến nơi</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div
                        class="sticky top-5 z-10 bg-sidebar/95 backdrop-blur-sm -mx-2 px-2 pb-2 space-y-4 border-b border-gray-100 m-0">
                    </div>
                    <div class="px-1">
                        <OrderList :orders="visibleOrders" :loading="orderLoading" :selected-id="selectedCurrentId"
                            @select="(o) => emit('select', { order: o, mode: 'current' })"
                            @load-more="loadMoreOrders" />
                    </div>
                </div>

                <!-- HISTORY TAB -->
                <div v-else class="space-y-4">
                    <div
                        class="sticky top-10 z-10 bg-sidebar/95 backdrop-blur-sm -mx-2 px-2 py-2 space-y-4 border-b border-gray-100 m-0">
                        <div class="flex items-center justify-between px-1">
                            <div>
                                <h2 class="text-xl font-lexend font-bold text-gray-900">Lịch sử</h2>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wider font-semibold">Các đơn đã
                                    qua
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span v-if="orderHistory?.length > 0"
                                    class="bg-gray-100 text-gray-600 text-xs font-bold px-2 py-0.5 rounded-full border border-gray-200">
                                    {{ orderHistory.length }} đơn
                                </span>
                                <span v-else class="text-xs text-gray-400 italic">Trống</span>
                                <ReloadButton :is-reloading="isReloading" @reload="reloadHistory()" />
                            </div>
                        </div>

                        <!-- filters for history -->
                        <div class="space-y-3">
                            <div class="relative group">
                                <Search
                                    class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400 group-focus-within:text-primary-600 transition-colors" />
                                <input v-model="kw" placeholder="Tìm trong lịch sử..."
                                    class="w-full h-10 rounded-xl bg-gray-50/50 border border-gray-200 pl-10 pr-4 text-sm focus:bg-white focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all outline-none"
                                    type="text" />
                            </div>

                            <div class="flex gap-2">
                                <div class="relative flex-1">
                                    <select v-model="sortByHistory"
                                        class="w-full h-9 rounded-lg bg-gray-50/50 border border-gray-200 px-3 text-xs font-medium cursor-pointer hover:bg-gray-100 transition-colors appearance-none outline-none">
                                        <option value="newest">Gần đây</option>
                                        <option value="oldest">Cũ hơn</option>
                                        <option value="highest-budget">Ngân sách cao nhất</option>
                                        <option value="lowest-budget">Ngân sách thấp nhất</option>
                                    </select>
                                    <ChevronDown
                                        class="absolute right-2 top-1/2 -translate-y-1/2 h-3 w-3 text-gray-400 pointer-events-none" />
                                </div>
                            </div>

                            <div class="flex flex-wrap gap-2 px-1">
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" class="hidden" value="completed" v-model="statusFilter" />
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-bold transition-all border',
                                        statusFilter.includes('completed')
                                            ? 'bg-green-100 text-green-700 border-green-200 shadow-sm ring-2 ring-green-800/70'
                                            : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400'
                                    ]">Hoàn thành</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" class="hidden" value="cancelled" v-model="statusFilter" />
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-bold transition-all border',
                                        statusFilter.includes('cancelled')
                                            ? 'bg-rose-100 text-rose-700 border-rose-200 shadow-sm ring-2 ring-rose-800/70'
                                            : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400'
                                    ]">Đã hủy</span>
                                </label>
                                <label class="flex items-center gap-1.5 cursor-pointer group">
                                    <input type="checkbox" class="hidden" value="expired" v-model="statusFilter" />
                                    <span :class="[
                                        'px-3 py-1 rounded-full text-[11px] font-bold transition-all border',
                                        statusFilter.includes('expired')
                                            ? 'bg-gray-100 text-gray-600 border-gray-200 shadow-sm ring-2 ring-gray-800/70'
                                            : 'bg-white text-gray-500 border-gray-300 hover:border-gray-400'
                                    ]">Hết hạn</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="px-1">
                        <OrderHistoryList :orders="visibleHistoryOrders" :loading="historyLoading"
                            :selected-id="selectedHistoryId"
                            @select="(o) => emit('select', { order: o, mode: 'history' })" @load-more="loadMoreHistory"
                            @reload="reloadHistory()" />
                    </div>
                </div>
            </Motion>
        </div>
    </div>
</template>
