<template>
    <section class="md:px-6 md:py-6 px-0 py-0">
        <header class="space-y-2">
            <h2 class="text-xl font-semibold text-gray-900">Mô tả chi tiết</h2>
            <p class="text-sm text-gray-500">
                Khám phá nội dung và các ưu điểm nổi bật của gói thiết kế.
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

        <section v-if="includedFiles.length" class="space-y-4">
            <h3 class="text-base font-semibold text-gray-900">Thiết kế đi kèm</h3>
            <ul class="grid gap-3 md:grid-cols-2">
                <li
                    v-for="file in includedFiles"
                    :key="file.id ?? file.name"
                    class="flex items-center justify-between gap-3 rounded-2xl border border-gray-100 bg-white px-4 py-3 shadow-xs"
                >
                    <div>
                        <p class="text-sm font-medium text-gray-800">
                            {{ file.name }}
                        </p>
                        <p class="text-xs text-gray-500">
                            {{ buildFileMeta(file) }}
                        </p>
                    </div>
                    <a
                        v-if="file.download_url"
                        :href="file.download_url"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex items-center rounded-full border border-primary-100 bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700 hover:border-primary-200 hover:text-primary-900"
                    >
                        Tải thử
                    </a>
                </li>
            </ul>
        </section>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';

import type { FileProduct, Tag } from '@/pages/home/types';

interface IncludedFile {
    id?: number | string | null;
    name: string;
    size?: string | null;
    format?: string | null;
    pages?: number | null;
    download_url?: string | null;
}

interface DetailProduct extends FileProduct {
    long_description?: string | null;
    highlights?: string[] | null;
    tags?: Tag[];
    included_files?: IncludedFile[] | null;
    total_pages?: number | null;
    total_files?: number | null;
    orientation?: string | null;
    dimensions?: string | null;
}

interface MetaEntry {
    label: string;
    value: string;
}

interface Props {
    fileProduct: DetailProduct;
    meta?: MetaEntry[];
}

const props = withDefaults(defineProps<Props>(), {
    meta: () => [],
});

const descriptionToShow = computed(
    () => props.fileProduct.long_description || props.fileProduct.description || ''
);

const descriptionParagraphs = computed(() =>
    descriptionToShow.value
        .split(/\n{2,}/)
        .map((paragraph) => paragraph.trim())
        .filter(Boolean)
);

const highlights = computed(() => props.fileProduct.highlights ?? []);

const includedFiles = computed(() => props.fileProduct.included_files ?? []);

const metaInformation = computed(() => {
    const meta = [...props.meta];

    if (props.fileProduct.total_pages) {
        meta.push({ label: 'Số trang', value: `${props.fileProduct.total_pages} trang` });
    }
    if (props.fileProduct.total_files) {
        meta.push({ label: 'Số file', value: `${props.fileProduct.total_files} tệp` });
    }
    if (props.fileProduct.orientation) {
        meta.push({ label: 'Định dạng', value: props.fileProduct.orientation });
    }
    if (props.fileProduct.dimensions) {
        meta.push({ label: 'Kích thước', value: props.fileProduct.dimensions });
    }

    return meta;
});

function buildFileMeta(file: IncludedFile): string {
    const meta: string[] = [];
    if (file.format) meta.push(file.format);
    if (file.size) meta.push(file.size);
    if (file.pages) meta.push(`${file.pages} trang`);
    return meta.join(' • ');
}
</script>
