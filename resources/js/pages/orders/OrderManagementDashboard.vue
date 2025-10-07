<script setup lang="ts">
import Sidebar from './layout/Sidebar.vue'
import OrderDetailPanel from './components/OrderDetailPanel.vue'
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import {
    ClientOrder,
    ClientOrderDetail,
    ClientOrderHistory,
    ClientOrderHistoryPayload,
    OrderDetailsPayload,
    Partner,
    SingleClientOrderPayload,
} from './types'
import RatingDialog from './components/RatingDialog.vue'
import {
    parseNextPage,
    stripPagingFromUrl,
    appendUniqueById,
} from './helper'
import Loading from '@/components/Loading.vue'
import ConfirmModal from '@/components/ConfirmModal.vue'
import { confirm } from '@/composables/useConfirm'
import { hideLoading, showLoading } from '@/composables/useLoading'
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'

const activeTab = ref<'current' | 'history'>('current')

const selectedOrder = ref<ClientOrder | null>(null)
const selectedMode = ref<'current' | 'history'>('current')
const detailsMap = ref<Record<number, { items: ClientOrderDetail[]; version?: number }>>({})
const showMobileDetail = ref(false)
let lastRequestedId: number | null = null

function syncSelectedOrder(freshOrders: ClientOrder[], isAppend: boolean) {
    if (!selectedOrder.value || selectedMode.value !== 'current') return

    const selectedId = selectedOrder.value.id

    const freshMatch = freshOrders.find(o => o.id === selectedId)
    if (freshMatch) {
        selectedOrder.value = freshMatch
        return
    }

    if (isAppend) {
        const existingMatch = currentOrders.value.find(o => o.id === selectedId)
        if (existingMatch) {
            selectedOrder.value = existingMatch
        }
    }

    if (!freshMatch && !isAppend) {
        fetchSingleOrder(selectedId)
    }
}

function fetchSingleOrder(orderId: number) {
    router.get(route('client-orders.dashboard'), { single_order: orderId }, {
        only: ['singleOrder'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (pageResp) => {
            const response = pageResp.props.singleOrder as SingleClientOrderPayload
            const order = (response?.data || response) as ClientOrder

            if (order && selectedOrder.value?.id === orderId) {
                selectedOrder.value = order

                if (order.status !== 'pending') {
                    console.log('Order status changed to:', order.status)
                }
            }
        },
        onError: (e) => {
            console.error('Failed to fetch single order:', e)
        },
    })
}

const currentOrders = ref<ClientOrder[]>([])
const currentNextPage = ref<number | null>(1)
const currentLoading = ref(false)
const loadedOrderIds = ref<Set<number>>(new Set())
const firstOrdersLoaded = ref(false)

const historyItems = ref<ClientOrderHistory[]>([])
const historyNextPage = ref<number | null>(1)
const historyLoading = ref(false)
const loadedHistoryIds = ref<Set<number>>(new Set())
let historyLoadedOnce = false

function initOrders() {
    if (firstOrdersLoaded.value) return
    loadOrders(1, false)
}

function loadOrders(page: number, append: boolean) {
    if (currentLoading.value) return
    currentLoading.value = true

    router.get(route('client-orders.dashboard'), { page }, {
        only: ['orderList'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (pageResp) => {
            const payload = pageResp.props.orderList as Paginated<ClientOrder>
            const incoming = payload?.data ?? []

            if (append) {
                appendUniqueById(currentOrders, incoming, loadedOrderIds)
            } else {
                currentOrders.value = [...incoming]
                loadedOrderIds.value = new Set(incoming.map(o => o.id))
                firstOrdersLoaded.value = true
            }

            syncSelectedOrder(incoming, append)

            currentNextPage.value = parseNextPage(payload)
            stripPagingFromUrl()
        },
        onError: (error) => {
            console.error('Failed to load orders:', error)
        },
        onFinish: () => {
            currentLoading.value = false
        },
    })
}

function loadMoreOrders() {
    if (!currentNextPage.value) return
    loadOrders(currentNextPage.value, true)
}

function refreshCurrentOrders() {
    if (currentLoading.value) return

    loadedOrderIds.value.clear()
    currentNextPage.value = 1

    loadOrders(1, false)
}

function fetchHistory(page: number, append = false) {
    if (historyLoading.value || page === null) return
    historyLoading.value = true

    router.get(route('client-orders.dashboard'), { history: 1, history_page: page }, {
        only: ['orderHistoryList'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (pageResp) => {
            const payload = pageResp.props.orderHistoryList as ClientOrderHistoryPayload
            const newData = Array.isArray((payload as any)?.data) ? (payload as any).data : []

            if (append) {
                appendUniqueById(historyItems as any, newData, loadedHistoryIds as any)
            } else {
                historyItems.value = [...newData]
                loadedHistoryIds.value = new Set(newData.map((h: any) => h.id))
            }

            if (selectedMode.value === 'history' && selectedOrder.value) {
                const selectedId = selectedOrder.value.id
                const freshMatch = newData.find((h: any) => h.id === selectedId)
                if (freshMatch) {
                    selectedOrder.value = freshMatch
                } else if (append) {
                    const existingMatch = historyItems.value.find(h => h.id === selectedId)
                    if (existingMatch) {
                        selectedOrder.value = existingMatch as any
                    }
                }
            }

            historyNextPage.value = parseNextPage(payload, 'history_page')
            historyLoadedOnce = true
            stripPagingFromUrl()
        },
        onError: (e) => {
            console.error('Failed to fetch history:', e)
        },
        onFinish: () => {
            historyLoading.value = false
        },
    })
}

function loadMoreHistory() {
    if (historyNextPage.value) {
        fetchHistory(historyNextPage.value, true)
    }
}

function reloadHistory() {
    loadedHistoryIds.value.clear()
    historyNextPage.value = 1
    fetchHistory(1, false)
}

function fetchDetails(id: number, reload = false) {
    if (id in detailsMap.value && !reload) return
    lastRequestedId = id

    router.get(route('client-orders.dashboard'), { active: id }, {
        only: ['orderListDetails'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            const payload = page.props.orderListDetails as OrderDetailsPayload

            if (!payload || payload.billId !== lastRequestedId) {
                if (!payload) {
                    detailsMap.value[id] = { items: [], version: undefined }
                }
                return
            }

            let items: ClientOrderDetail[] = []
            if (Array.isArray(payload.items)) {
                items = payload.items as ClientOrderDetail[]
            } else if ((payload.items as any)?.data) {
                items = (payload.items as any).data
            }

            detailsMap.value[payload.billId] = { items, version: payload.version }

            // Also refresh the order itself when reloading details
            if (reload && selectedOrder.value?.id === id) {
                fetchSingleOrder(id)
            }
        },
        onError: (e) => {
            console.error('Failed to fetch details for order', id, e)
        },
    })
}

function refreshDetails(id: number | undefined) {
    if (!id) return
    fetchDetails(id, true)
}

function handleSelect(payload: { order: ClientOrder; mode: 'current' | 'history' }) {
    selectedOrder.value = payload.order
    selectedMode.value = payload.mode
    fetchDetails(payload.order.id)
    showMobileDetail.value = true
}

const applicants = computed<ClientOrderDetail[]>(() => {
    const id = selectedOrder.value?.id
    if (!id) return []
    return detailsMap.value[id]?.items ?? []
})

async function handleConfirmChoosePartner(partner?: Partner | null) {
    if (!partner || !selectedOrder.value?.id) return

    const orderId = selectedOrder.value.id

    const ok = await confirm({
        title: `Bạn có muốn chọn đối tác (${partner.partner_profile?.partner_name ?? partner.name})?`,
        message: `Xác nhận chốt đơn sẽ mở khóa chat với đối tác và <b class="font-lexend">không thể chọn lại đối tác khác cho đơn này.</b>`,
        okText: 'Chốt đơn luôn!',
        cancelText: 'Ko, chưa chốt'
    })

    if (!ok) return

    useForm({
        order_id: orderId,
        partner_id: partner.id,
    }).post(route('client-orders.confirm-partner'), {
        preserveScroll: true,
        onBefore: () => {
            showLoading({ title: 'Đang tải', message: 'Đợi xíu nhé' })
        },
        onSuccess: () => {
            delete detailsMap.value[orderId]
            fetchDetails(orderId, true)
            refreshCurrentOrders()
        },
        onFinish: () => {
            hideLoading(true)
        }
    })
}

async function handleCancelOrder() {
    if (!selectedOrder.value?.id) return

    const orderId = selectedOrder.value.id

    const ok = await confirm({
        title: 'Bạn có chắc chắn muốn hủy đơn không?',
        message: 'Lượt hủy này có thể sẽ tăng tỉ lệ hủy đơn của tài khoản của bạn. Bạn sẽ phải đợi xác nhận nếu đã lỡ chốt đơn với một đối tác bất kỳ. <b class="font-lexend">Trong trường hợp đó, hãy chat với đối tác trước khi thực hiện hủy nhé!</b>',
        okText: 'Hủy đơn ngay',
        cancelText: 'Ko hủy, tôi lỡ tay'
    })

    if (!ok) return

    useForm({
        order_id: orderId,
    }).post(route('client-orders.cancel'), {
        preserveScroll: true,
        onBefore: () => {
            showLoading({ title: 'Đang tải', message: 'Đợi xíu nhé' })
        },
        onSuccess: () => {
            selectedOrder.value = null
            delete detailsMap.value[orderId]
            refreshCurrentOrders()
        },
        onFinish: () => {
            hideLoading(true)
        }
    })
}

const showRatingDialog = ref(false)
const rating = ref(0)
const comment = ref('')

function openRating() {
    if (selectedMode.value === 'history') {
        showRatingDialog.value = true
    }
}

function submitRating(payload: { rating: number; comment: string }) {
    console.log('[submit rating]', payload)
    showRatingDialog.value = false
    rating.value = 0
    comment.value = ''
}

function pollForUpdates() {
    if (selectedOrder.value?.id && selectedOrder.value?.status === 'pending') {
        fetchDetails(selectedOrder.value.id, true)
    }

    if (firstOrdersLoaded.value && !currentLoading.value) {
        router.get(route('client-orders.dashboard'), { page: 1 }, {
            only: ['orderList'],
            preserveState: true,
            preserveScroll: true,
            onSuccess: (pageResp) => {
                const payload = pageResp.props.orderList as Paginated<ClientOrder>
                const fresh = payload?.data ?? []

                if (selectedOrder.value && selectedMode.value === 'current') {
                    const selectedId = selectedOrder.value.id
                    const freshMatch = fresh.find(o => o.id === selectedId)
                    if (freshMatch) {
                        selectedOrder.value = freshMatch
                    }
                }

                const hasNewOrders = fresh.some(order => !loadedOrderIds.value.has(order.id))

                if (hasNewOrders || fresh.length !== currentOrders.value.length) {
                    refreshCurrentOrders()
                }
            },
        })
    }
}

watch(activeTab, (tab) => {
    if (tab === 'history' && !historyLoadedOnce) {
        fetchHistory(1, false)
    }
})

const loadingForSidebar = computed(() =>
    activeTab.value === 'current' ? currentLoading.value : historyLoading.value
)

let pollInterval: number | undefined

onMounted(() => {
    initOrders()
    pollInterval = window.setInterval(pollForUpdates, 64_000)
})

onBeforeUnmount(() => {
    if (pollInterval) {
        clearInterval(pollInterval)
    }
})
</script>

<template>
    <Head title="Đơn hàng của tôi" />
    <ClientHeaderLayout :show-footer="false">
        <div class="flex h-[90vh] bg-background w-full overflow-visible">
            <div :class="[showMobileDetail ? 'hidden md:block' : 'block', 'w-full md:w-auto']">
                <Sidebar
                    :orderList="currentOrders"
                    :history-loading="loadingForSidebar"
                    :order-loading="loadingForSidebar"
                    :orderHistory="historyItems"
                    v-model:activeTab="activeTab"
                    @select="handleSelect"
                    @load-history-more="loadMoreHistory"
                    @reload="reloadHistory"
                    @load-orders-more="loadMoreOrders"
                    @reload-orders="refreshCurrentOrders"
                />
            </div>

            <div :class="[showMobileDetail ? 'block' : 'hidden md:block']" class="flex-1">
                <OrderDetailPanel
                    @reload-detail="refreshDetails"
                    :mode="selectedMode"
                    :order="selectedOrder"
                    :applicants="applicants"
                    @back="showMobileDetail = false"
                    @rate="openRating"
                    @cancel-order="handleCancelOrder"
                    @confirm-choose-partner="handleConfirmChoosePartner"
                />
            </div>

            <RatingDialog
                v-model:showRatingDialog="showRatingDialog"
                v-model:rating="rating"
                v-model:comment="comment"
                @submit="submitRating"
            />
        </div>
    </ClientHeaderLayout>
</template>
