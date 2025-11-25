<template>
    <section class="space-y-8 rounded-3xl border border-gray-100 bg-white md:px-6 md:py-6 px-0 py-0 shadow-sm lg:p-10">
        <header class="space-y-2">
            <h2 class="text-xl font-semibold text-gray-900">Thông tin chi tiết</h2>
            <p class="text-sm text-gray-500">
                Tổng quan thiết bị và các điểm nổi bật khi thuê tại Sukientot.
            </p>
        </header>

        <div v-if="descriptionToShow" class="prose prose-sm max-w-none text-gray-700">
            <p v-for="(paragraph, index) in descriptionParagraphs" :key="index" class="whitespace-pre-line">
                <template v-html="paragraph"></template>
            </p>
        </div>

        <section v-if="highlights.length" class="space-y-3">
            <h3 class="text-base font-semibold text-gray-900">Điểm nổi bật</h3>
            <ul class="grid gap-3 md:grid-cols-2">
                <li
                    v-for="(item, index) in highlights"
                    :key="index"
                    class="flex items-start gap-3 rounded-2xl border border-primary-50 bg-primary-10/40 px-4 py-3 text-sm text-primary-900"
                >
                    <span class="mt-1 inline-flex size-6 flex-none items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">★</span>
                    <span>{{ item }}</span>
                </li>
            </ul>
        </section>

        <section v-if="metaInformation.length" class="space-y-3">
            <h3 class="text-base font-semibold text-gray-900">Thông tin chính</h3>
            <dl class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                <div
                    v-for="(entry, index) in metaInformation"
                    :key="index"
                    class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm"
                >
                    <dt class="font-semibold text-gray-700">{{ entry.label }}</dt>
                    <dd class="mt-1 text-gray-600">{{ entry.value }}</dd>
                </div>
            </dl>
        </section>

        <section v-if="includedItems.length" class="space-y-4">
            <h3 class="text-base font-semibold text-gray-900">Danh sách thiết bị đi kèm</h3>
            <ul class="grid gap-3 md:grid-cols-2">
                <li
                    v-for="item in includedItems"
                    :key="item.id ?? item.name"
                    class="flex items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-white px-4 py-3 shadow-xs"
                >
                    <div>
                        <p class="text-sm font-medium text-gray-800">
                            {{ item.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ buildItemMeta(item) }}
                        </p>
                    </div>
                </li>
            </ul>
        </section>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import type { IncludedFile, RentProduct } from '@/pages/rent/types';

interface MetaEntry {
    label: string;
    value: string;
}

interface Props {
    rentProduct: RentProduct;
    meta?: MetaEntry[];
}

const props = withDefaults(defineProps<Props>(), {
    meta: () => [],
});

const descriptionToShow = computed(
    () => props.rentProduct.long_description || props.rentProduct.description || ''
);

const descriptionParagraphs = computed(() =>
    descriptionToShow.value
        .split(/\n{2,}/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean)
);

const highlights = computed(() => props.rentProduct.highlights ?? []);

const includedItems = computed(() => props.rentProduct.included_files ?? []);

const metaInformation = computed(() => {
    const meta = [...props.meta];

    if (props.rentProduct.total_pages) {
        meta.push({ label: 'Số trang thiết kế', value: `${props.rentProduct.total_pages} trang` });
    }
    if (props.rentProduct.total_files) {
        meta.push({ label: 'Số hạng mục', value: `${props.rentProduct.total_files} hạng mục` });
    }
    if (props.rentProduct.orientation) {
        meta.push({ label: 'Định dạng', value: props.rentProduct.orientation });
    }
    if (props.rentProduct.dimensions) {
        meta.push({ label: 'Kích thước', value: props.rentProduct.dimensions });
    }

    return meta;
});

function buildItemMeta(item: IncludedFile): string {
    const meta: string[] = [];
    if (item.format) meta.push(item.format);
    if (item.size) meta.push(item.size);
    if (item.pages) meta.push(`${item.pages} trang`);
    return meta.join(' • ');
}
</script>
