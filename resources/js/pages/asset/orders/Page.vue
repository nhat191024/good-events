<template>
    <Head title="Đơn hàng thiết kế" />

    <ClientHeaderLayout>
        <section class="bg-white pb-16 pt-6">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
                <header class="flex flex-col gap-2">
                    <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
                        <ul class="flex flex-wrap items-center gap-2">
                            <li>
                                <Link :href="route('asset.home')" class="hover:text-primary-800">
                                    Kho thiết kế
                                </Link>
                            </li>
                            <li class="flex items-center gap-2">
                                <span aria-hidden="true" class="text-primary-400">›</span>
                                <span class="text-primary-800">Đơn hàng của tôi</span>
                            </li>
                        </ul>
                    </nav>

                    <div class="flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
                        <div>
                            <h1 class="font-lexend text-3xl font-semibold text-gray-900">
                                Quản lý đơn hàng thiết kế
                            </h1>
                            <p class="text-sm text-gray-500">
                                Theo dõi trạng thái thanh toán và tải lại thiết kế bạn đã mua.
                            </p>
                        </div>
                    </div>
                </header>

                <div v-if="hasOrders || initialLoading" class="grid gap-6 lg:grid-cols-[minmax(0,320px)_minmax(0,1fr)]">
                    <OrderSidebar
                        :orders="orders"
                        :selected-id="selectedId"
                        :loading="loadingOrders"
                        :has-more="hasMore"
                        :initializing="initialLoading"
                        @select="handleSelect"
                        @load-more="loadMore"
                    />

                    <OrderDetailPanel
                        :order="displayedOrder"
                        :loading="detailLoading && !displayedOrder"
                        @repay="handleRepay"
                    />
                </div>

                <div v-else class="flex flex-col items-center justify-center gap-4 rounded-2xl border border-dashed border-gray-200 bg-gray-50 px-6 py-16 text-center">
                    <div class="flex h-16 w-16 items-center justify-center rounded-full bg-primary-100 text-primary-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" class="h-8 w-8" stroke="currentColor" stroke-width="1.5">
                            <path d="M4 6h16M4 12h16M4 18h10" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </div>
                    <div class="space-y-2">
                        <h2 class="font-lexend text-xl font-semibold text-gray-800">
                            Bạn chưa có đơn hàng nào
                        </h2>
                        <p class="max-w-md text-sm text-gray-500">
                            Các đơn thiết kế đã mua sẽ xuất hiện tại đây. Khám phá kho thiết kế và đặt mua thiết kế phù hợp ngay bây giờ.
                        </p>
                    </div>
                    <Link
                        :href="route('asset.discover')"
                        class="inline-flex h-11 items-center justify-center rounded-xl bg-primary-600 px-6 text-sm font-medium text-white shadow-sm hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-400"
                    >
                        Khám phá thiết kế
                    </Link>
                </div>
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import axios from 'axios';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import OrderSidebar from './components/OrderSidebar.vue';
import OrderDetailPanel from './components/OrderDetailPanel.vue';

import type { AssetOrder, AssetOrdersPageProps, AssetOrdersPayload } from './types';

const props = withDefaults(defineProps<AssetOrdersPageProps>(), {
    orders: undefined,
    activeOrder: null,
    statusOptions: () => [],
});

interface PaginationMeta {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    [key: string]: unknown;
}

const orders = ref<AssetOrder[]>([]);
const pagination = ref<PaginationMeta | null>(null);
const loadingOrders = ref(false);
const detailLoading = ref(false);
const initialized = ref(false);
const selectedId = ref<number | null>(null);
const repaying = ref(false);

const hasOrders = computed(() => orders.value.length > 0);
const hasMore = computed(() => {
    if (!pagination.value) {
        return false;
    }
    return pagination.value.current_page < pagination.value.last_page;
});
const initialLoading = computed(() => !initialized.value && loadingOrders.value);

const selectedOrder = computed<AssetOrder | null>(() => {
    if (!selectedId.value) return null;
    return orders.value.find((order) => order.id === selectedId.value) ?? null;
});

const displayedOrder = computed<AssetOrder | null>(() => {
    const order = selectedOrder.value;
    if (!order) return null;
    if (!repaying.value) return order;
    return {
        ...order,
        can_repay: false,
    };
});

function parseOrdersPayload(payload: AssetOrdersPayload): { data: AssetOrder[]; meta: PaginationMeta | null } {
    if (!payload) {
        return { data: [], meta: null };
    }

    if (Array.isArray(payload)) {
        return { data: payload, meta: null };
    }

    if (typeof payload === 'object' && 'data' in payload) {
        const data = Array.isArray((payload as any).data) ? (payload as any).data as AssetOrder[] : [];
        const meta = (payload as any).meta ?? null;
        return { data, meta };
    }

    return { data: [], meta: null };
}

function hydrateOrders(payload: AssetOrdersPayload, append = false) {
    const { data, meta } = parseOrdersPayload(payload);

    if (append) {
        const existingIds = new Set(orders.value.map((order) => order.id));
        const additions = data.filter((order) => !existingIds.has(order.id));
        if (additions.length > 0) {
            orders.value = [...orders.value, ...additions];
        }
    } else {
        orders.value = [...data];
    }

    if (!append) {
        pagination.value = meta;
    } else if (meta) {
        pagination.value = meta;
    }

    if (orders.value.length === 0) {
        selectedId.value = null;
    } else if (!selectedId.value) {
        selectedId.value = orders.value[0].id;
    }

    initialized.value = true;
}

function updateQueryParam(orderId: number | null) {
    if (typeof window === 'undefined') return;

    try {
        const url = new URL(window.location.href);
        if (orderId) {
            url.searchParams.set('bill_id', String(orderId));
        } else {
            url.searchParams.delete('bill_id');
        }
        window.history.replaceState({}, '', url.toString());
    } catch (error) {
        console.warn('Không thể cập nhật tham số trên URL', error);
    }
}

async function fetchOrders(page = 1, append = false) {
    if (loadingOrders.value) return;

    loadingOrders.value = true;

    router.get(route('client-orders.asset.dashboard'), { page }, {
        only: ['orders'],
        preserveState: true,
        preserveScroll: true,
        onSuccess: (pageResponse) => {
            hydrateOrders(pageResponse.props.orders as AssetOrdersPayload, append);
        },
        onError: (error) => {
            console.error('Không thể tải danh sách đơn hàng thiết kế', error);
        },
        onFinish: () => {
            loadingOrders.value = false;
            initialized.value = true;
        },
    });
}

async function fetchOrderDetail(orderId: number) {
    detailLoading.value = true;
    try {
        const response = await axios.get(route('client-orders.asset.details', { bill: orderId }));
        const payload = (response.data?.data ?? response.data) as AssetOrder | null;
        if (payload) {
            upsertOrder(payload, { select: true, toTop: true });
        }
    } catch (error) {
        console.error('Không thể tải chi tiết đơn hàng', error);
    } finally {
        detailLoading.value = false;
    }
}

function upsertOrder(order: AssetOrder, options: { select?: boolean; toTop?: boolean } = {}) {
    const index = orders.value.findIndex((item) => item.id === order.id);

    if (index >= 0) {
        orders.value.splice(index, 1, order);
    } else if (options.toTop) {
        orders.value = [order, ...orders.value];
    } else {
        orders.value = [...orders.value, order];
    }

    if (options.select) {
        selectedId.value = order.id;
    }
}

function loadMore() {
    if (!pagination.value) return;

    const nextPage = pagination.value.current_page + 1;
    if (nextPage > pagination.value.last_page) return;

    fetchOrders(nextPage, true);
}

function handleSelect(order: AssetOrder) {
    if (selectedId.value === order.id) return;

    selectedId.value = order.id;
    updateQueryParam(order.id);
    fetchOrderDetail(order.id);
}

async function handleRepay(order: AssetOrder) {
    if (repaying.value) return;
    repaying.value = true;

    try {
        const response = await axios.post(route('client-orders.asset.repay', { bill: order.id }));
        const checkoutUrl = response.data?.checkoutUrl as string | undefined;

        if (checkoutUrl) {
            window.location.href = checkoutUrl;
        } else {
            window.alert('Không thể khởi tạo thanh toán lại. Vui lòng thử lại sau.');
        }
    } catch (error: any) {
        const message = error?.response?.data?.message ?? 'Không thể thanh toán lại đơn hàng, vui lòng thử lại sau.';
        window.alert(message);
        console.error('Không thể thanh toán lại đơn hàng', error);
    } finally {
        repaying.value = false;
    }
}

onMounted(() => {
    if (props.orders) {
        hydrateOrders(props.orders);
    } else {
        fetchOrders();
    }

    if (props.activeOrder) {
        upsertOrder(props.activeOrder, { select: true, toTop: true });
    }

    const initialBillId = (() => {
        if (typeof window === 'undefined') return null;
        try {
            const url = new URL(window.location.href);
            const value = Number(url.searchParams.get('bill_id'));
            return Number.isFinite(value) && value > 0 ? value : null;
        } catch {
            return null;
        }
    })();

    if (initialBillId) {
        selectedId.value = initialBillId;
        updateQueryParam(initialBillId);
        fetchOrderDetail(initialBillId);
    }
});

watch(selectedId, (value) => {
    if (!value) {
        updateQueryParam(null);
    }
});
</script>
