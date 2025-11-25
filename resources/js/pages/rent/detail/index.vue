<template>
    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <section class="w-full bg-white pb-16 pt-6">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li>
                            <Link :href="route('rent.home')" class="hover:text-primary-800">Kho thuê thiết bị</Link>
                        </li>
                        <li v-if="categorySlug" class="flex items-center gap-2">
                            <span aria-hidden="true" class="text-primary-400">›</span>
                            <Link :href="route('rent.category', { category_slug: categorySlug })" class="hover:text-primary-800">
                                {{ props.rentProduct.category?.name || 'Danh mục' }}
                            </Link>
                        </li>
                        <li class="flex items-center gap-2">
                            <span aria-hidden="true" class="text-primary-400">›</span>
                            <span class="text-primary-800">Chi tiết thiết bị</span>
                        </li>
                    </ul>
                </nav>

                <DetailHero
                    :rent-product="props.rentProduct"
                    :preview-images="previewMedia"
                    :contact-hotline="props.contactHotline"
                />

                <ProductOverview :rent-product="props.rentProduct" :meta="computedMeta" />

                <PreviewGallery :media="galleryMedia" />

                <RelatedProducts :items="relatedItems" />
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import DetailHero from './components/DetailHero.vue';
import ProductOverview from './components/ProductOverview.vue';
import PreviewGallery from './components/PreviewGallery.vue';
import RelatedProducts from './components/RelatedProducts.vue';

import type { Category, RentProduct, Tag, PreviewMedia } from '@/pages/rent/types';

interface IncludedFile {
    id?: number | string | null;
    name: string;
    size?: string | null;
    format?: string | null;
    pages?: number | null;
    download_url?: string | null;
}

interface DetailRentProduct extends RentProduct {
    long_description?: string | null;
    highlights?: string[] | null;
    preview_images?: PreviewMedia[] | string[];
    included_files?: IncludedFile[] | null;
    tags?: Tag[];
    total_pages?: number | null;
    total_files?: number | null;
    orientation?: string | null;
    dimensions?: string | null;
    updated_human?: string | null;
}

type RelatedPayload =
    | RentProduct[]
    | { data?: RentProduct[] | null }
    | Paginated<RentProduct>
    | undefined;

interface DetailPageProps {
    rentProduct: DetailRentProduct;
    related?: RelatedPayload;
    contactHotline?: string | null;
}

const props = withDefaults(defineProps<DetailPageProps>(), {
    related: undefined,
    contactHotline: null,
});

function normalizePreview(entry: PreviewMedia | string, index: number): PreviewMedia | null {
    if (typeof entry === 'string') {
        return entry ? { id: index, url: entry, thumbnail: entry } : null;
    }
    if (entry && entry.url) {
        return {
            id: entry.id ?? index,
            url: entry.url,
            thumbnail: entry.thumbnail ?? entry.url,
            alt: entry.alt ?? props.rentProduct.name,
        };
    }
    return null;
}

function toArray<T>(payload: RelatedPayload | { data?: T[] | null } | T[] | undefined): T[] {
    if (!payload) return [];
    if (Array.isArray(payload)) return payload;
    if ('data' in payload && Array.isArray(payload.data)) {
        return payload.data as T[];
    }
    if ('data' in (payload as any) && Array.isArray((payload as any).data)) {
        return (payload as any).data as T[];
    }
    return [];
}

const previewMedia = computed(() => {
    const list = props.rentProduct.preview_images ?? [];
    return list
        .map((entry, index) => normalizePreview(entry, index))
        .filter((item): item is PreviewMedia => Boolean(item?.url));
});

const galleryMedia = computed(() => previewMedia.value.slice(0, 6));

const relatedItems = computed(() => toArray<RentProduct>(props.related));

const categorySlug = computed(() => props.rentProduct.category?.slug ?? null);

const pageTitle = computed(() => `${props.rentProduct.name} - Thuê thiết bị Sukientot`);

const computedMeta = computed(() => {
    const meta: Array<{ label: string; value: string }> = [];
    if (props.rentProduct.category?.name) {
        meta.push({ label: 'Danh mục', value: props.rentProduct.category.name });
    }
    if (props.rentProduct.tags?.length) {
        meta.push({
            label: 'Từ khóa',
            value: props.rentProduct.tags.map((tag) => tag.name).join(', '),
        });
    }
    return meta;
});

export type { DetailPageProps };
</script>
