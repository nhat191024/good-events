<template>
    <aside class="flex flex-col gap-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        <header class="space-y-1">
            <h2 class="text-lg font-semibold text-gray-900">Tóm tắt đơn hàng</h2>
            <p class="text-xs text-gray-500">Kiểm tra thông tin sản phẩm và chi phí dự kiến.</p>
        </header>

        <div class="flex items-start gap-4 rounded-2xl border border-gray-100 bg-gray-50 p-4">
            <img
                :src="getImg(props.productThumbnail)"
                :alt="props.fileProduct.name"
                class="h-20 w-20 flex-none rounded-2xl object-cover"
                loading="lazy"
            />
            <div class="flex flex-1 flex-col gap-1">
                <p class="text-sm font-semibold text-gray-900">{{ props.fileProduct.name }}</p>
                <p class="text-xs text-gray-500">{{ props.fileProduct.category?.name }}</p>
                <div class="mt-2 text-sm font-semibold text-primary-700">{{ props.priceText }}</div>
            </div>
        </div>

        <dl class="space-y-3 text-sm text-gray-700">
            <div class="flex items-center justify-between">
                <dt>Tạm tính</dt>
                <dd>{{ props.subtotalText }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt>Ưu đãi</dt>
                <dd class="text-primary-600">{{ props.discountText }}</dd>
            </div>
            <div class="flex items-center justify-between">
                <dt>Thuế VAT</dt>
                <dd>{{ props.vatText }}</dd>
            </div>
            <div class="flex items-center justify-between border-t border-dashed border-gray-200 pt-3 text-base font-semibold text-gray-900">
                <dt>Thanh toán</dt>
                <dd>{{ props.totalText }}</dd>
            </div>
        </dl>

        <section v-if="props.paymentMethodOptions.length" class="space-y-3 rounded-2xl border border-gray-100 bg-gray-50 p-4 text-xs text-gray-600">
            <p class="font-semibold text-gray-800">Thông tin thanh toán</p>
            <div v-for="method in props.paymentMethodOptions" :key="method.code" class="space-y-1">
                <p class="text-sm font-semibold text-primary-700">{{ method.name }}</p>
                <p v-if="method.description">{{ method.description }}</p>
            </div>
        </section>
    </aside>
</template>

<script setup lang="ts">
import type { Category, FileProduct } from '@/pages/home/types';

import type { PaymentMethod } from '@/pages/asset/purchase/types';
import { getImg } from '@/pages/booking/helper';

interface PurchaseSummaryProps {
    fileProduct: FileProduct & { category?: Category | null };
    priceText: string;
    subtotalText: string;
    discountText: string;
    vatText: string;
    totalText: string;
    productThumbnail: string;
    paymentMethodOptions: PaymentMethod[];
}

const props = withDefaults(defineProps<PurchaseSummaryProps>(), {
    paymentMethodOptions: () => [],
});
</script>
