<template>
    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <section class="w-full bg-white pb-16 pt-6">
            <div class="mx-auto flex w-full max-w-5xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
                <PurchaseBreadcrumbs :file-product="props.fileProduct" />

                <div class="grid gap-8 lg:grid-cols-[1.6fr,1fr]">
                    <PurchaseForm :form="form" :payment-method-options="paymentMethodOptions" @submit="submit" @back="goBack" />

                    <PurchaseSummary
                        :file-product="props.fileProduct"
                        :price-text="priceText"
                        :subtotal-text="subtotalText"
                        :discount-text="discountText"
                        :vat-text="vatText"
                        :total-text="totalText"
                        :product-thumbnail="productThumbnail"
                        :payment-method-options="paymentMethodOptions"
                    />
                </div>
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';

import { hideLoading, showLoading } from '@/composables/useLoading';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { formatPrice } from '@/lib/helper';

import PurchaseBreadcrumbs from '@/pages/asset/purchase/components/PurchaseBreadcrumbs.vue';
import PurchaseForm from '@/pages/asset/purchase/components/PurchaseForm.vue';
import PurchaseSummary from '@/pages/asset/purchase/components/PurchaseSummary.vue';
import type { PurchaseFormFields, PurchasePageProps } from '@/pages/asset/purchase/types';

import { inject } from "vue";

const route = inject('route') as any;

const props = withDefaults(defineProps<PurchasePageProps>(), {
    buyer: () => ({}),
    paymentMethods: () => [],
    totals: () => ({}),
});

const form = useForm<PurchaseFormFields>({
    slug: props.fileProduct.slug,
    name: props.buyer?.name ?? '',
    email: props.buyer?.email ?? '',
    phone: props.buyer?.phone ?? '',
    company: props.buyer?.company ?? '',
    tax_code: props.buyer?.tax_code ?? '',
    note: props.buyer?.note ?? '',
    payment_method: props.buyer?.payment_method ?? props.paymentMethods[0]?.code ?? '',
});

watch(
    () => props.fileProduct.slug,
    (next) => {
        form.slug = next;
    }
);

watch(
    () => props.buyer,
    (buyer) => {
        form.name = buyer?.name ?? '';
        form.email = buyer?.email ?? '';
        form.phone = buyer?.phone ?? '';
        form.company = buyer?.company ?? '';
        form.tax_code = buyer?.tax_code ?? '';
        form.note = buyer?.note ?? '';
        if (buyer?.payment_method) {
            form.payment_method = buyer.payment_method;
        }
    },
    { deep: true }
);

const paymentMethodOptions = computed(() => props.paymentMethods);

const priceNumber = computed(() => Number(props.fileProduct.price) || 0);

const subtotalText = computed(() => formatCurrency(props.totals?.subtotal ?? priceNumber.value));
const discountText = computed(() => {
    const discount = props.totals?.discount ?? 0;
    if (!discount) return '0 đ';
    return `- ${formatCurrency(discount)}`;
});
const vatText = computed(() => formatCurrency(props.totals?.vat ?? priceNumber.value * 0.1));
const totalText = computed(() => formatCurrency(props.totals?.total ?? priceNumber.value));
const priceText = computed(() => formatCurrency(priceNumber.value));

const productThumbnail = computed(() => props.fileProduct.image ?? `https://ui-avatars.com/api/?name=${encodeURIComponent(props.fileProduct.name)}&background=2563EB&color=ffffff&size=256`);

const pageTitle = computed(() => `Thanh toán ${props.fileProduct.name}`);

function formatCurrency(value: number) {
    return `${formatPrice(value)} đ`;
}

function submit() {
    const loadingPromise = showLoading({ title: 'Đang xử lý đơn hàng', message: 'Chúng tôi đang xác nhận thanh toán…' });
    let dismissed = false;
    const closeLoading = (result: boolean) => {
        if (!dismissed) {
            hideLoading(result);
            dismissed = true;
        }
    };

    form.post(route('asset.buy.confirm'), {
        onSuccess: () => {
            closeLoading(true);
        },
        onError: () => {
            closeLoading(false);
        },
        onFinish: () => {
            closeLoading(false);
        },
    });

    loadingPromise.finally(() => {
        // ensure promise chain settled before leaving scope
    });
}

function goBack() {
    window.history.back();
}

export type { PurchasePageProps } from '@/pages/asset/purchase/types';
</script>
