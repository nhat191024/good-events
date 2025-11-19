<template>
    <section class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-gray-50">
                <img
                    :src="getImg(coverImage)"
                    :alt="fileProduct.name"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
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

        <aside class="flex h-max flex-col gap-6 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
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
                <p class="mt-1 text-xs text-primary-700/70">
                    Bao gồm đầy đủ file thiết kế chất lượng cao cùng thiết kế hướng dẫn triển khai.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <a
                    v-if="isPurchased && downloadUrl"
                    :href="downloadUrl"
                    target="_blank"
                    rel="noopener"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Tải xuống ngay
                </a>
                <Link
                    v-else
                    :href="route('asset.buy', { slug: fileProduct.slug })"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Mua ngay
                </Link>
                <a
                    :href="`tel:0901234567`"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-white px-4 py-3 text-primary-600 font-semibold text-base shadow-sm transition hover:bg-primary-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Gọi: 0901 234 567
                </a>
                <!-- <Button variant="outline" class="w-full border-primary-100 text-primary-700 hover:border-primary-200 hover:text-primary-900">
                    Thêm vào danh sách yêu thích
                </Button> -->
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
    </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button';
import { formatDate, formatPrice } from '@/lib/helper';

import type { FileProduct, Tag } from '@/pages/home/types';
import { getImg } from '@/pages/booking/helper';

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
}

interface Props {
    fileProduct: DetailProduct;
    previewImages?: PreviewMedia[];
    downloadUrl?: string | null;
    isPurchased?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    previewImages: () => [],
    downloadUrl: null,
    isPurchased: false,
});

const selectedImage = ref<string | null>(null);

const placeholderImage = computed(
    () => `https://ui-avatars.com/api/?name=${encodeURIComponent(props.fileProduct.name)}&background=1F73D8&color=ffffff&size=512`
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

function setPrimary(url: string) {
    selectedImage.value = url;
}
</script>
