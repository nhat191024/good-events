<script setup lang="ts">

import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import { Package, History, SlidersHorizontal, Filter, ChevronDown, Calendar } from 'lucide-vue-next'
import OrderList from '../components/OrderList.vue'
import OrderHistoryList from '../components/OrderHistoryList.vue'

import { createSearchFilter } from '../../../lib/search-filter'
import { ClientOrder, ClientOrderHistory } from '../types'
import { debounce } from '../helper'
import ReloadButton from '../components/ReloadButton.vue'

const isReloading = ref(false)

const props = defineProps<{
    orderList: ClientOrder[]
    orderHistory: ClientOrderHistory[]
    historyLoading?: boolean
    orderLoading?: boolean
    // orderList: any,
    // orderHistory: any|null
}>()

const orderHistory = computed(() => props.orderHistory)

const activeTab = defineModel<'current' | 'history'>('activeTab', { default: 'current' })

const showAdvancedFilter = ref(false)
const kw = ref('')
const sortBy = ref<'newest' | 'oldest' | 'most-applicants' | 'highest-budget' | 'lowest-budget'>('newest')
const sortByHistory = ref<'newest' | 'oldest' | 'highest-budget' | 'lowest-budget'>('newest')

const statusFilter = ref<string[]>(['pending', 'confirmed', 'cancelled', 'completed', 'expired'])
const locationsFilter = ref<string[]>([])

function sortOrders(list: ClientOrder[]) {
    const klone = [...list]
    switch (sortBy.value) {
        case 'newest':
            return klone.sort((a, b) => Date.parse(b.updated_at) - Date.parse(a.updated_at))
        case 'oldest':
            return klone.sort((a, b) => Date.parse(a.updated_at) - Date.parse(b.updated_at))
        case 'most-applicants':
            return klone.sort((a, b) => (b.partners.count || 0) - (a.partners.count || 0))
        case 'highest-budget':
            return klone.sort((a, b) => (b.final_total || 0) - (a.final_total || 0))
        case 'lowest-budget':
            return klone.sort((a, b) => (a.final_total || 0) - (b.final_total || 0))
        default:
            return klone
    }
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
    // console.log('in computed: ',list);

    try {
        const fn = createSearchFilter(['code', 'note', 'status', 'address', 'start_time', 'end_time', 'final_total', 'category.name', 'category.parent.name', 'event.name'], kw.value)
        list = list.filter(fn)
    } catch { }

    if (statusFilter.value.length) {
        list = list.filter(o => statusFilter.value.includes(o.status))
    }

    if (locationsFilter.value.length) {
        list = list.filter(o => locationsFilter.value.includes(o.address))
    }

    return sortOrders(list)
})

const visibleHistoryOrders = computed(() => {

    let list = [...props.orderHistory]
    // console.log('in computed: ',list);

    try {
        const fn = createSearchFilter(['code', 'note', 'status', 'address', 'start_time', 'end_time', 'final_total', 'category.name', 'category.parent.name', 'event.name'], kw.value)
        list = list.filter(fn)
    } catch { }

    if (statusFilter.value.length) {
        list = list.filter(o => statusFilter.value.includes(o.status))
    }

    if (locationsFilter.value.length) {
        list = list.filter(o => locationsFilter.value.includes(o.address))
    }

    return sortHistoryOrders(list)
})

function onDocClick(e: MouseEvent) {
    const el = (e.target as HTMLElement)
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
    // isReloading.value = true
    // setTimeout(() => {
    //     isReloading.value = false
    // }, 3000)
}

const loadMoreOrders = () => {
    emit('load-orders-more')
    // isReloading.value = true
    // setTimeout(() => {
    //     isReloading.value = false
    // }, 3000)
}

onMounted(() => document.addEventListener('click', onDocClick))

onBeforeUnmount(() => document.removeEventListener('click', onDocClick))

const emit = defineEmits<{
    (e: 'select', payload: { order: any; mode: 'current' | 'history' }): void,
    (e: 'load-history'): void
    (e: 'reload'): void
    (e: 'reload-orders'): void
    (e: 'load-history-more'): void
    (e: 'load-orders-more'): void
}>()
</script>

<template>
    <div
        class="w-full md:w-96 md:flex-shrink-0 bg-sidebar border-r border-sidebar-border overflow-y-auto scrollbar-hide h-full">
        <div class="p-2 md:p-6">
            <!-- tabs -->
            <div
                class="grid grid-cols-2 gap-2 mb-3 md:mb-6 items-center sticky top-1 rounded bg-white/30 backdrop-blur-md w-full md:h-fit h-[50px]">
                <button type="button"
                    class="h-10 rounded-md border border-border flex items-center justify-center gap-2"
                    :class="activeTab === 'current' ? 'bg-primary-700 text-white' : 'bg-card'"
                    @click="activeTab = 'current'">
                    <Package class="h-4 w-4" />
                    Đơn hiện tại
                </button>
                <button type="button"
                    class="h-10 rounded-md border border-border flex items-center justify-center gap-2"
                    :class="activeTab === 'history' ? 'bg-primary-700 text-white' : 'bg-card'"
                    @click="activeTab = 'history'">
                    <History class="h-4 w-4" />
                    Lịch sử
                </button>
            </div>

            <!-- CURRENT TAB -->
            <div v-if="activeTab === 'current'" class="">
                <div class="space-y-1 md:space-y-4 top-12 rounded-md sticky bg-white/30 backdrop-blur-md p-2 mb-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-lexend font-semibold text-sidebar-foreground">Đơn hàng của bạn</h2>
                        <span class="bg-secondary text-secondary-foreground text-xs px-2.5 py-1 rounded">{{
                            visibleOrders.length }} đơn</span>
                        <ReloadButton :is-reloading="isReloading" @reload="reloadOrders()" />
                    </div>

                    <!-- filters -->
                    <div class="space-y-2 md:space-y-4 mb-1">

                        <!-- search keyword -->
                        <input v-model="kw" placeholder="Tìm theo tiêu đề, lĩnh vực, mô tả, ngân sách..."
                            class="w-full h-9 rounded-md bg-white border border-gray-200 px-3 text-sm" type="text" />

                        <!-- sort select -->
                        <div class="space-y-2">
                            <!-- <label class="text-xs text-muted-foreground">Sắp xếp theo</label> -->
                            <select v-model="sortBy"
                                class="w-full h-9 rounded-md bg-white border border-gray-200 px-3 text-sm">
                                <option value="newest">Gần đây</option>
                                <option value="oldest">Cũ hơn</option>
                                <option v-if="activeTab == 'current'" value="most-applicants">Nhiều ứng viên nhất
                                </option>
                                <option value="highest-budget">Ngân sách cao nhất</option>
                                <option value="lowest-budget">Ngân sách thấp nhất</option>
                            </select>
                        </div>

                        <!-- advanced filter toggle -->
                        <div id="orders-adv-filter" class="relative">
                            <div class="flex flex-row space-x-4">
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" class="size-4" value="pending" v-model="statusFilter">
                                    Đang chờ
                                </label>
                                <label class="flex items-center gap-2 text-sm">
                                    <input type="checkbox" class="size-4" value="confirmed" v-model="statusFilter">
                                    Đã chốt
                                </label>
                            </div>
                            <div v-if="showAdvancedFilter"
                                class="absolute left-0 mt-2 w-80 bg-popover text-popover-foreground border border-border rounded-lg shadow-lg p-4 z-10">
                                <div class="space-y-3">
                                    <label class="text-sm font-medium">Trạng thái đơn hàng</label>
                                    <div class="space-y-2">
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" class="size-4" value="pending"
                                                v-model="statusFilter">
                                            Đang chờ
                                        </label>
                                        <label class="flex items-center gap-2 text-sm">
                                            <input type="checkbox" class="size-4" value="paid" v-model="statusFilter">
                                            Đã chốt
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <OrderList :orders="visibleOrders" :loading="orderLoading"
                    @select="o => emit('select', { order: o, mode: 'current' })" @load-more="loadMoreOrders" />
            </div>

            <!-- HISTORY TAB -->
            <div v-else>
                <div class="space-y-1 md:space-y-4 top-12 rounded-md sticky bg-white/30 backdrop-blur-md p-2 mb-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-lexend font-semibold text-sidebar-foreground">
                            Lịch sử đơn hàng
                        </h2>
                        <div class="flex items-center gap-2">
                            <span v-if="orderHistory?.length > 0"
                                class="bg-secondary text-secondary-foreground text-xs px-2.5 py-1 rounded">
                                {{ orderHistory.length }} đơn
                            </span>
                            <span v-else class="bg-secondary text-secondary-foreground text-xs px-2.5 py-1 rounded">
                                Trống
                            </span>
                            <ReloadButton :is-reloading="isReloading" @reload="reloadHistory()" />
                        </div>
                    </div>

                    <!-- filters for history -->
                    <div class="space-y-2 md:space-y-4 mb-1">
                        <input v-model="kw" placeholder="Tìm theo mã, ghi chú, trạng thái, địa chỉ..."
                            class="w-full h-9 rounded-md bg-white border border-gray-200 px-3 text-sm" type="text" />

                        <select v-model="sortByHistory"
                            class="w-full h-9 rounded-md bg-white border border-gray-200 px-3 text-sm">
                            <option value="newest">Gần đây</option>
                            <option value="oldest">Cũ hơn</option>
                            <option value="highest-budget">Ngân sách cao nhất</option>
                            <option value="lowest-budget">Ngân sách thấp nhất</option>
                        </select>

                        <div class="flex flex-row space-x-4">
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" class="size-4" value="completed" v-model="statusFilter" />
                                Hoàn thành
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" class="size-4" value="cancelled" v-model="statusFilter" />
                                Đã hủy
                            </label>
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" class="size-4" value="expired" v-model="statusFilter" />
                                Hết hạn
                            </label>
                        </div>
                    </div>
                </div>

                <OrderHistoryList :orders="visibleHistoryOrders" :loading="historyLoading"
                    @select="o => emit('select', { order: o, mode: 'history' })" @load-more="loadMoreHistory"
                    @reload="reloadHistory()" />
            </div>
        </div>
    </div>
</template>
