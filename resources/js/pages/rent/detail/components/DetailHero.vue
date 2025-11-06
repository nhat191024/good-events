<template>
    <section class="grid gap-8 lg:grid-cols-2">
        <div class="space-y-5">
            <div class="overflow-hidden rounded-3xl border border-gray-100 bg-gray-50">
                <img
                    :src="getImg(coverImage)"
                    :alt="rentProduct.name"
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
                        :alt="`Xem trước ${rentProduct.name}`"
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
                    v-if="rentProduct.category"
                    class="inline-flex w-max items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700"
                >
                    {{ rentProduct.category.name }}
                </span>
                <h1 class="text-2xl font-semibold text-gray-900">
                    {{ rentProduct.name }}
                </h1>
                <p v-if="rentProduct.description" class="text-sm text-gray-500">
                    {{ rentProduct.description }}
                </p>
                <dl class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                    <div>
                        <dt class="font-medium text-gray-700">Cập nhật</dt>
                        <dd>{{ updatedAtText }}</dd>
                    </div>
                    <div v-if="rentProduct.created_at">
                        <dt class="font-medium text-gray-700">Ngày tạo</dt>
                        <dd>{{ createdAtText }}</dd>
                    </div>
                </dl>
            </div>

            <div class="rounded-2xl bg-primary-50/60 p-5 ring-1 ring-primary-100">
                <p class="text-sm font-medium text-primary-800">Giá thuê tham khảo</p>
                <p class="mt-1 text-3xl font-semibold text-primary-900">{{ priceText ?? 'Liên hệ' }}</p>
                <p class="mt-1 text-xs text-primary-700/70">
                    Vui lòng liên hệ hotline để nhận báo giá chi tiết và lịch giao nhận nhanh nhất.
                </p>
            </div>

            <div class="flex flex-col gap-3">
                <a
                    :href="hotlineHref"
                    class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2"
                >
                    Gọi ngay: {{ hotlineDisplay }}
                </a>
                <p class="text-xs text-gray-500">
                    Hotline hỗ trợ 24/7 của Sukientot sẵn sàng tư vấn giải pháp thuê vật tư phù hợp cho sự kiện của bạn.
                </p>
            </div>

            <ul class="space-y-2 rounded-2xl border border-gray-100 bg-gray-50 p-5 text-sm text-gray-600">
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                    Thiết bị được kiểm tra trước khi bàn giao.
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                    Hỗ trợ set up tại địa điểm tổ chức.
                </li>
                <li class="flex items-center gap-3">
                    <span class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                    Lịch giao nhận linh hoạt theo yêu cầu.
                </li>
            </ul>
        </aside>
    </section>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';

import { formatDate, formatPrice } from '@/lib/helper';

import type { RentProduct, Tag, PreviewMedia } from '@/pages/rent/types';
import { getImg } from '@/pages/booking/helper';

interface Props {
    rentProduct: RentProduct & { tags?: Tag[] };
    previewImages?: PreviewMedia[];
    contactHotline?: string | null;
}

const props = withDefaults(defineProps<Props>(), {
    previewImages: () => [],
    contactHotline: null,
});

const selectedImage = ref<string | null>(null);

const placeholderImage = computed(
    () => `https://ui-avatars.com/api/?name=${encodeURIComponent(props.rentProduct.name)}&background=1F73D8&color=ffffff&size=512`
);

const coverImage = computed(() => selectedImage.value ?? props.previewImages[0]?.url ?? props.rentProduct.image ?? placeholderImage.value);

const secondaryPreviews = computed(() => props.previewImages);

const tags = computed(() => props.rentProduct.tags ?? []);

const updatedAtText = computed(() => {
    if (props.rentProduct.updated_human) return props.rentProduct.updated_human;
    if (props.rentProduct.updated_at) return formatDate(String(props.rentProduct.updated_at));
    return 'Đang cập nhật';
});

const createdAtText = computed(() => {
    if (props.rentProduct.created_at) return formatDate(String(props.rentProduct.created_at));
    return '—';
});

const priceText = computed(() => {
    const value = Number(props.rentProduct.price);
    return Number.isFinite(value) ? `${formatPrice(value)} đ` : 'Liên hệ';
});

const hotlineDisplay = computed(() => props.contactHotline?.trim() || '0901 234 567');

const hotlineHref = computed(() => {
    const numeric = hotlineDisplay.value.replace(/[^0-9+]/g, '');
    return `tel:${numeric}`;
});

function setPrimary(url: string) {
    selectedImage.value = url;
}
</script>
