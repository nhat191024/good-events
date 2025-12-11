<template>
    <section class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-gray-50">
                <img :src="getImg(coverImage)" :alt="fileProduct.name" class="h-full w-full object-cover" loading="lazy" />
            </div>

            <div v-if="secondaryPreviews.length" class="flex flex-wrap gap-3">
                <button
                    v-for="media in secondaryPreviews"
                    :key="media.id ?? media.url"
                    type="button"
                    class="group overflow-hidden rounded-2xl border border-transparent shadow-sm transition hover:-translate-y-0.5 hover:border-primary-200"
                    @click="setPrimary(media.url)"
                >
                    <img
                        :src="getImg(media.thumbnail ?? media.url)"
                        :alt="`Xem trước ${fileProduct.name}`"
                        class="h-20 w-28 object-cover brightness-95 transition group-hover:brightness-100"
                    />
                </button>
            </div>

            <div v-if="tags.length" class="flex flex-wrap gap-2">
                <span
                    v-for="tag in tags"
                    :key="tag.slug"
                    class="rounded-full border border-primary-100 bg-primary-10 px-3 py-1 text-xs font-semibold text-primary-700"
                >
                    {{ tag.name }}
                </span>
            </div>
        </div>

        <aside class="flex h-max flex-col gap-6 rounded-3xl bg-white px-0 py-0 md:px-6 md:py-6">
            <div class="space-y-2">
                <span
                    v-if="fileProduct.category"
                    class="inline-flex w-max items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700"
                >
                    {{ fileProduct.category.name }}
                </span>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ fileProduct.name }}
                </h1>
                <dl class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                    <div>
                        <dt class="font-medium text-gray-700">Cập nhật</dt>
                        <dd>{{ updatedAtText }}</dd>
                    </div>
                    <div v-if="fileProduct.download_count !== undefined && fileProduct.download_count !== null">
                        <dt class="font-medium text-gray-700">Lượt mua</dt>
                        <dd>{{ fileProduct.download_count.toLocaleString('vi-VN') }}</dd>
                    </div>
                    <div v-if="fileProduct.created_at">
                        <dt class="font-medium text-gray-700">Ngày tạo</dt>
                        <dd>{{ createdAtText }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl bg-primary-50/60 p-5 ring-1 ring-primary-100">
                <p class="text-sm font-medium text-primary-800">Giá trọn gói</p>
                <p class="mt-1 text-3xl font-semibold text-primary-900">{{ priceText }}</p>
                <p class="mt-1 text-xs text-primary-700/70">Bao gồm đầy đủ file thiết kế chất lượng cao cùng thiết kế hướng dẫn triển khai.</p>
            </div>

            <div class="flex flex-col gap-3">
                <a
                    v-if="isPurchased && downloadZipUrl"
                    :href="downloadZipUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Tải (ZIP)
                </a>
                <Link
                    v-else
                    :href="route('asset.buy', { slug: fileProduct.slug })"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                >
                    Mua ngay
                </Link>
                <button
                    type="button"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-white px-4 py-3 text-base font-semibold text-primary-600 shadow-sm transition hover:bg-primary-50 focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2 focus-visible:outline-none"
                    @click="isContactModalOpen = true"
                >
                    Gọi: {{ hotlineDisplay }}
                </button>
            </div>

            <ul class="space-y-2 rounded-2xl border border-gray-100 bg-gray-50 p-5 text-sm text-gray-600">
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                    File nguồn chất lượng cao, dễ chỉnh sửa.
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                    Phù hợp cho nhiều loại hình sự kiện.
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                    Hỗ trợ nhanh chóng khi cần điều chỉnh.
                </li>
            </ul>
        </aside>

        <div
            v-if="isContactModalOpen"
            class="fixed inset-0 z-50 flex items-center justify-center bg-gray-900/60 p-4"
            @click="isContactModalOpen = false"
        >
            <div
                class="relative w-full max-w-sm rounded-2xl bg-white p-6 shadow-xl"
                @click.stop
            >
                <button
                    type="button"
                    class="absolute right-3 top-3 rounded-full p-2 text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                    @click="isContactModalOpen = false"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path
                            fill-rule="evenodd"
                            d="M10 8.586 4.293 2.879 2.879 4.293 8.586 10l-5.707 5.707 1.414 1.414L10 11.414l5.707 5.707 1.414-1.414L11.414 10l5.707-5.707-1.414-1.414L10 8.586Z"
                            clip-rule="evenodd"
                        />
                    </svg>
                </button>
                <h3 class="text-lg font-semibold text-gray-900">Chọn cách liên hệ</h3>
                <p class="mt-1 text-sm text-gray-600">Vui lòng chọn kênh liên hệ với tư vấn viên.</p>
                <div class="mt-5 flex flex-col gap-3">
                    <a
                        :href="zaloHref"
                        target="_blank"
                        rel="noopener"
                        class="inline-flex w-full items-center justify-center rounded-lg border border-primary-200 bg-primary-50 px-4 py-3 text-base font-semibold text-primary-700 transition hover:border-primary-300 hover:bg-primary-100 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                    >
                        Liên hệ qua Zalo
                    </a>
                    <a
                        :href="hotlineHref"
                        class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                    >
                        Gọi trực tiếp
                    </a>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

import { formatDate, formatPrice } from '@/lib/helper';

import { getImg } from '@/pages/booking/helper';
import type { FileProduct, Tag } from '@/pages/home/types';
import type { AppSettings } from '@/types';

interface PreviewMedia {
    id?: number | string | null;
    url: string;
    thumbnail?: string | null;
}

interface DetailProduct extends FileProduct {
    tags?: Tag[];
    preview_images?: PreviewMedia[] | string[];
    download_count?: number | null;
    updated_human?: string | null;
    included_files?: { id?: number | string | null; name: string; download_url?: string | null }[] | null;
}

interface Props {
    fileProduct: DetailProduct;
    previewImages?: PreviewMedia[];
    downloadUrl?: string | null;
    downloadZipUrl?: string | null;
    isPurchased?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    previewImages: () => [],
    downloadUrl: null,
    downloadZipUrl: null,
    isPurchased: false,
});

const selectedImage = ref<string | null>(null);
const isContactModalOpen = ref(false);
const page = usePage();
const appSettings = computed(() => page.props.app_settings as AppSettings | undefined);
const hotlineDisplay = computed(() => appSettings.value?.contact_hotline?.trim() || '0901 234 567');

const placeholderImage = computed(
    () => `https://ui-avatars.com/api/?name=${encodeURIComponent(props.fileProduct.name)}&background=1F73D8&color=ffffff&size=512`,
);

const coverImage = computed(() => selectedImage.value ?? props.previewImages[0]?.url ?? props.fileProduct.image ?? placeholderImage.value);

const secondaryPreviews = computed(() => props.previewImages);

const tags = computed(() => props.fileProduct.tags ?? []);

const updatedAtText = computed(() => {
    if (props.fileProduct.updated_human) return props.fileProduct.updated_human;
    if (props.fileProduct.updated_at) return formatDate(String(props.fileProduct.updated_at));
    return 'Đang cập nhật';
});

const createdAtText = computed(() => {
    if (props.fileProduct.created_at) return formatDate(String(props.fileProduct.created_at));
    return '—';
});

const priceText = computed(() => {
    const value = Number(props.fileProduct.price);
    return Number.isFinite(value) ? `${formatPrice(value)} đ` : 'Liên hệ';
});

const hotlineNumeric = computed(() => hotlineDisplay.replace(/[^0-9+]/g, ''));

const hotlineHref = computed(() => `tel:${hotlineNumeric.value}`);

const zaloHref = computed(() => `http://zalo.me/${hotlineNumeric.value}`);

const downloadUrls = computed(() => {
    const files = props.fileProduct.included_files ?? [];
    return files.map((entry) => entry.download_url).filter((url): url is string => Boolean(url));
});

function triggerDownloadAll() {
    const urls = downloadUrls.value || [];
    if (!urls.length) return;
    for (const url of urls) {
        const a = document.createElement('a');
        a.href = url;
        a.target = '_blank';
        a.rel = 'noopener noreferrer';
        a.download = '';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    }
}

function setPrimary(url: string) {
    selectedImage.value = url;
}
</script>
