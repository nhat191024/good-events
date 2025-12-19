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
    OrderStatus,
    Partner,
    SingleClientOrderPayload,
} from './types'
import RatingDialog from './components/RatingDialog.vue'
import {
    parseNextPage,
    stripPagingFromUrl,
    appendUniqueById,
    debounce,
} from './helper'

import { confirm } from '@/composables/useConfirm'
import { hideLoading, showLoading } from '@/composables/useLoading'
import { showToast } from '@/composables/useToast'
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import { formatPrice } from '@/lib/helper'

import PartnerProfilePreview from './components/PartnerProfilePreview.vue'
import axios from 'axios'

const activeTab = ref<'current' | 'history'>('current')

const selectedOrder = ref<ClientOrder | null>(null)
const selectedMode = ref<'current' | 'history'>('current')
const detailsMap = ref<Record<number, { items: ClientOrderDetail[]; version?: number }>>({})
const showMobileDetail = ref(false)
const historyStatuses = new Set([
    OrderStatus.COMPLETED,
    OrderStatus.CANCELLED,
    OrderStatus.EXPIRED,
])
const queryOrderId = ref<number | null>(null)
const hasAppliedOrderParam = ref(false)
let lastRequestedId: number | null = null

function parseInitialOrderId() {
    if (typeof window === 'undefined') return
    const value = new URL(window.location.href).searchParams.get('order')
    if (!value) {
        queryOrderId.value = null
        return
    }
    const parsed = Number(value)
    queryOrderId.value = Number.isFinite(parsed) && parsed > 0 ? parsed : null
}

function updateOrderQueryParam(orderId: number | null) {
    if (typeof window === 'undefined') return
    const url = new URL(window.location.href)
    if (orderId) {
        url.searchParams.set('order', String(orderId))
    } else {
        url.searchParams.delete('order')
    }
    window.history.replaceState({}, '', url)
}

function resolveMode(status: OrderStatus): 'current' | 'history' {
    return historyStatuses.has(status) ? 'history' : 'current'
}

function ensureOrderCached(order: ClientOrder) {
    const mode = resolveMode(order.status)

    if (mode === 'current') {
        if (!currentOrders.value.some(o => o.id === order.id)) {
            currentOrders.value.unshift(order)
            loadedOrderIds.value.add(order.id)
        }
        return
    }

    if (!historyItems.value.some(o => o.id === order.id)) {
        historyItems.value.unshift(order as any)
        loadedHistoryIds.value.add(order.id)
    }
}

function applySelection(order: ClientOrder, mode?: 'current' | 'history') {
    const resolvedMode = mode ?? resolveMode(order.status)
    selectedOrder.value = order
    selectedMode.value = resolvedMode
    activeTab.value = resolvedMode
    showMobileDetail.value = true
    updateOrderQueryParam(order.id)
    fetchDetails(order.id)
}

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

function fetchSingleOrder(orderId: number, options: { select?: boolean } = {}) {
    router.get(route('client-orders.dashboard'), { order: orderId }, {
        only: ['singleOrder'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (pageResp) => {
            const response = pageResp.props.singleOrder as SingleClientOrderPayload
            const order = (response?.data || response) as ClientOrder

            if (!order) {
                if (options.select) {
                    hasAppliedOrderParam.value = true
                }
                return
            }

            ensureOrderCached(order)

            if (options.select) {
                applySelection(order)
                hasAppliedOrderParam.value = true
                return
            }

            if (selectedOrder.value?.id === orderId) {
                selectedOrder.value = order
                const nextMode = resolveMode(order.status)

                if (selectedMode.value !== nextMode) {
                    applySelection(order, nextMode)
                }
            }
        },
        onError: (e) => {
            console.error('Failed to fetch single order:', e)
        },
    })
}

function tryApplyOrderFromQuery() {
    if (!queryOrderId.value || hasAppliedOrderParam.value) return

    const id = queryOrderId.value

    const currentMatch = currentOrders.value.find(o => o.id === id)
    if (currentMatch) {
        applySelection(currentMatch, 'current')
        hasAppliedOrderParam.value = true
        return
    }

    const historyMatch = historyItems.value.find(h => h.id === id)
    if (historyMatch) {
        applySelection(historyMatch as any, 'history')
        hasAppliedOrderParam.value = true
        return
    }

    fetchSingleOrder(id, { select: true })
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
            tryApplyOrderFromQuery()

            currentNextPage.value = parseNextPage(payload)
            stripPagingFromUrl()
            if (selectedOrder.value?.id) {
                updateOrderQueryParam(selectedOrder.value.id)
            }
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
            tryApplyOrderFromQuery()
            stripPagingFromUrl()
            if (selectedOrder.value?.id) {
                updateOrderQueryParam(selectedOrder.value.id)
            }
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

            stripPagingFromUrl(['page', 'history_page', 'active'])
            if (selectedOrder.value?.id) {
                updateOrderQueryParam(selectedOrder.value.id)
            }
        },
        onError: (e) => {
            console.error('Failed to fetch details for order', id, e)
        },
    })
}

function refreshDetails(order: ClientOrder | undefined) {
    if (!order?.id || activeTab.value !== 'current') {
        return
    } else {
        if (order.status == OrderStatus.COMPLETED) {
            return
        }
        fetchDetails(order.id, true)
    }
}

function handleSelect(payload: { order: ClientOrder; mode: 'current' | 'history' }) {
    applySelection(payload.order, payload.mode)
}

const applicants = computed<ClientOrderDetail[]>(() => {
    const id = selectedOrder.value?.id
    if (!id) return []
    return detailsMap.value[id]?.items ?? []
})

async function getVoucherDiscountAmount(voucher_code?: string | null, partner?: Partner | null) {
    if (!voucher_code || !selectedOrder.value?.id) return 0

    const { data } = await axios.post(route('client-orders.get-voucher-discount-amount'), {
        voucher_input: voucher_code,
        order_id: selectedOrder.value.id,
        partner_id: partner?.id,
    })

    if (data?.status === false) return 0
    return data?.discount ?? 0
}

async function handleConfirmChoosePartner(
    partner?: Partner | null,
    total?: number | null,
    voucher_code?: string | null
) {
    if (!partner || !selectedOrder.value?.id) return

    try {
        showLoading({
            title: 'Đang kiểm tra mã giảm giá',
            message: 'Đợi xíu nhé...',
        })

        const orderId = selectedOrder.value.id
        let voucherDiscountAmount = 0;
        try {
            voucherDiscountAmount = await getVoucherDiscountAmount(voucher_code, partner)
        } catch (error) {
            console.error(error);
        } finally {
            hideLoading()
        }

        let finalTotal = (total ?? 0) - voucherDiscountAmount
        if (finalTotal < 0) finalTotal = 0

        const ok = await confirm({
            title: `Bạn có muốn chọn đối tác (${partner.partner_profile?.partner_name ?? partner.name})?`,
            message: `
              Xác nhận chốt đơn sẽ mở khóa chat với đối tác và
              <b class="font-lexend">không thể chọn lại đối tác khác cho đơn này.</b>
              <br>Đối tác trả giá: <span class="text-red-800 font-bold text-md">
              ${formatPrice(total ?? 0)}đ ${(voucherDiscountAmount > 0) ? '<br>Giá đã giảm từ mã giảm giá: ' + formatPrice(finalTotal) + 'đ (-' + formatPrice(voucherDiscountAmount) + 'đ)' : ''}</span>
              <br> Bạn có chấp nhận mức giá này không?
            `,
            okText: 'Chốt đơn luôn!',
            cancelText: 'Ko, chưa chốt',
        })

        if (!ok) return

        debounce(() => {
            showLoading({ title: 'Đang tải', message: 'Đợi xíu nhé' })
        }, 1, { leading: false, trailing: true })();

        useForm({
            order_id: orderId,
            partner_id: partner.id,
            voucher_code: voucher_code
        }).post(route('client-orders.confirm-partner'), {
            preserveScroll: true,
            onSuccess: () => {
                delete detailsMap.value[orderId]
            },
            onFinish: () => {
                debounce(() => {
                    fetchDetails(orderId, true);
                }, 1000, { leading: false, trailing: true })();

                debounce(() => {
                    refreshCurrentOrders();
                    hideLoading(true);
                }, 3000, { leading: false, trailing: true })();
            }
        })
    } catch (err) {
        console.error('error in handleConfirmChoosePartner', err)
        hideLoading()
        showNotice('Có lỗi xảy ra', 'Không thể lấy thông tin mã giảm giá, thử lại sau!')
    } finally {
        hideLoading()
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
        onSuccess: (page) => {
            selectedOrder.value = null
            delete detailsMap.value[orderId]
            updateOrderQueryParam(null)
            refreshCurrentOrders()

            const flash = page.props.flash as any
            if (flash?.success) {
                showToast(flash.success)
            } else if (flash?.error) {
                showToast({ message: flash.error, type: 'error' })
            }
        },
        onFinish: () => {
            hideLoading(true)
        }
    })
}

const selectedUserId = ref<number | null>(null)
const isPartnerProfileOpen = ref(false)

async function handleViewPartnerProfile(partnerId: number) {
    selectedUserId.value = partnerId
    isPartnerProfileOpen.value = true
}

function closePartnerProfile() {
    isPartnerProfileOpen.value = false
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
    const partnerId = (selectedOrder.value as any)?.partner?.id
        ?? (selectedOrder.value as any)?.partner_id
        ?? (applicants.value?.[0] as any)?.partner?.id

    if (!partnerId) {
        console.warn('không tìm thấy partnerId để chấm điểm')
        showRatingDialog.value = false
        return
    }

    const form = useForm({
        order_id: selectedOrder.value?.id ?? null,
        partner_id: partnerId,
        rating: payload.rating,
        comment: payload.comment,
        recommend: true, // test
    })

    form.post(route('client-orders.submit-review'), {
        preserveScroll: true,
        onSuccess: () => {
            if (selectedOrder.value) {
                ; (selectedOrder.value as any).reviewed = true
                    ; (selectedOrder.value as any).user_rating = payload.rating
                    ; (selectedOrder.value as any).user_comment = payload.comment
            }
            showRatingDialog.value = false
            showToast('Đã gửi đánh giá của bạn thành công!')
        },
        onError: (e) => {
            console.error('[submit rating] lỗi', e)
            showToast({
                message: 'Gửi đánh giá không thành công. Vui lòng kiểm tra lại!',
                type: 'error'
            })
        },
        onBefore: () => {
            showLoading({ title: 'Đang tải lên đánh giá của bạn', message: 'Đợi xíu nhé' })
        },
        onFinish: () => {
            // optional: refresh lại chi tiết để sync số liệu
            // fetchDetails(selectedOrder.value?.id, true)
            reloadHistory()
            hideLoading(true)
        },
    })
}

function pollForUpdates() {
    if (selectedOrder.value?.id && selectedOrder.value?.status === OrderStatus.PENDING) {
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
    parseInitialOrderId()
    initOrders()
    pollInterval = window.setInterval(() => {
        pollForUpdates()
        refreshDetails(selectedOrder.value ?? undefined)
    }, 64_000)
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
        <div class="flex h-[calc(100vh-4rem)] bg-background w-full overflow-visible">
            <div :class="[showMobileDetail ? 'hidden md:block' : 'block', 'w-full md:w-auto']">
                <Sidebar :orderList="currentOrders" :history-loading="loadingForSidebar"
                    :order-loading="loadingForSidebar" :orderHistory="historyItems"
                    :selected-order-id="selectedOrder?.id ?? null" :selected-mode="selectedMode"
                    v-model:activeTab="activeTab" @select="handleSelect" @load-history-more="loadMoreHistory"
                    @reload="reloadHistory" @load-orders-more="loadMoreOrders" @reload-orders="refreshCurrentOrders" />
            </div>

            <div :class="[showMobileDetail ? 'block' : 'hidden md:block']" class="flex-1">
                <OrderDetailPanel @reload-detail="refreshDetails" :mode="selectedMode" :order="selectedOrder"
                    :applicants="applicants" @back="showMobileDetail = false" @rate="openRating"
                    @cancel-order="handleCancelOrder"
                    @confirm-choose-partner="(partner, total, voucher_code) => handleConfirmChoosePartner(partner, total, voucher_code)"
                    @view-partner-profile="handleViewPartnerProfile($event)" />
            </div>

            <RatingDialog v-model:showRatingDialog="showRatingDialog" v-model:rating="rating" v-model:comment="comment"
                @submit="submitRating" />
        </div>
    </ClientHeaderLayout>

    <PartnerProfilePreview v-model:open="isPartnerProfileOpen" :user-id="selectedUserId" />
</template>
