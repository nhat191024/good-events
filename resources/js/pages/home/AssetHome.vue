<template>
    <Head class="font-lexend" title="Kho thiết kế thiết kế" />

    <ClientAppHeaderLayout :background-class-names="'bg-blue-100'">

        <HeroBanner
            :header-text="heroHeaderText"
            v-model="search"
            :banner-images="heroBannerImages"
            :bg-color-class="'bg-[linear-gradient(180deg,#6bafff_0%,rgb(129,187,255)_51.50151983037725%,rgba(74,144,226,0)_100%)]'"
        />

        <PartnerCategoryIcons :categories="categories" />

        <motion.div
            class="w-full pt-3 bg-white flex gap-2 justify-center flex-wrap"
            :initial="tagSectionMotion.initial"
            :while-in-view="tagSectionMotion.visible"
            :viewport="tagSectionMotion.viewport"
            :transition="tagSectionMotion.transition"
        >
            <motion.div
                v-for="(item, index) in tagItems"
                :key="item.slug ?? item.id"
                class="inline-flex"
                :initial="tagItemMotion.initial"
                :while-in-view="tagItemMotion.visible"
                :viewport="tagItemMotion.viewport"
                :transition="getTagTransition(index)"
                :while-hover="tagInteractions.hover"
                :while-tap="tagInteractions.tap"
            >
                <Link :href="route('asset.discover', { q: item.slug })">
                    <Button v-text="item.name" :size="'sm'" :variant="'outline'"
                        :class="'ring ring-primary-100 text-primary-800 bg-primary-10 hover:bg-primary-50'">
                    </Button>
                </Link>
            </motion.div>
        </motion.div>

        <CardListLayout :href="route('asset.discover')" :name="'Thiết kế mới gần đây'" :show-section="true">
            <CardItem v-for="item in fileProductList"
                :route-href="route('asset.show', { file_product_slug: item.slug, category_slug: item.category.slug })"
                :key="item.id" :card-item="item || []" />
        </CardListLayout>

        <motion.div
            class="w-full pb-6 bg-white flex gap-2 justify-center flex-wrap"
            :initial="ctaMotion.initial"
            :while-in-view="ctaMotion.visible"
            :viewport="ctaMotion.viewport"
            :transition="ctaMotion.transition"
        >
            <motion.div
                :while-hover="ctaInteractions.hover"
                :while-tap="ctaInteractions.tap"
                :transition="ctaMotion.transition"
            >
                <Link :href="route('asset.discover')">
                    <Button :size="'default'" :variant="'outline'" :class="'hover:bg-primary-50'">Xem thêm sản phẩm khác</Button>
                </Link>
            </motion.div>
        </motion.div>

    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import CardListLayout from './layouts/CardListLayout.vue';

import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';

import { PartnerCategoryItems } from './partials/PartnerCategoryIcons/type';
import { AssetCardItemProps, Category, FileProduct, Tag } from './types';

import { createSearchFilter } from '@/lib/search-filter';

import CardItem from './components/CardItem/index.vue';
import { Button } from '@/components/ui/button';
interface BannerImage {
    image_tag?: string | null;
}

interface BannerImageWrapper {
    data: BannerImage[];
}

interface Props {
    fileProducts: Paginated<FileProduct>;
    tags: Paginated<Tag>;
    categories: Paginated<Category>;
    settings: {
        app_name: string;
        banner_images: BannerImageWrapper;
        hero_title?: string | null;
    };
}

const props = defineProps<Props>();

const search = ref('');

const heroBannerImages = computed(() => props.settings.banner_images.data ?? []);
const heroHeaderText = computed(() => props.settings.hero_title ?? 'Trải nghiệm kho thiết kế thiết kế');

// console.log('file products ',props.fileProducts.data);
// console.log('tags ',props.tags.data);
// console.log('categories ',props.categories.data);

const filteredFileProducts = computed(() => {
    const data = props.fileProducts.data ?? []
    const q = search.value.trim()
    if (!q) return data

    const filter = createSearchFilter<FileProduct>(['name', 'slug'], q)
    return data.filter(filter)
})

const categories = computed<PartnerCategoryItems[]>(() =>
    (props.categories.data ?? []).map((item) => {
        return {
            id: item.id,
            name: item.name,
            slug: item.slug,
            icon: '',
            image: item.image ?? '',
            href: route('asset.category', { category_slug: item.slug })
        }
    })
);

const tagItems = computed(() => props.tags.data ?? []);

const fileProductList = computed<AssetCardItemProps[]>(() =>
    (filteredFileProducts.value ?? []).map((item) => {
        return {
            id: item.id,
            name: item.name,
            slug: item.slug,
            image: item.image ?? null,
            description: item.description ?? '',
            category: item.category
        }
    })
);

const tagSectionMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const tagItemMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.3 },
} as const;

const tagInteractions = {
    hover: {
        scale: 1.04,
        transition: { type: 'spring', duration: 0.45, bounce: 0.35 },
    },
    tap: {
        scale: 0.95,
        transition: { type: 'spring', duration: 0.4, bounce: 0.2 },
    },
} as const;

const getTagTransition = (index: number) => ({
    duration: 0.55,
    delay: Math.min(index * 0.04, 0.28),
    ease: 'easeOut',
});

const ctaMotion = {
    initial: { opacity: 0.5, y: 30 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.65, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const ctaInteractions = {
    hover: {
        scale: 1.03,
        transition: { type: 'spring', duration: 0.5, bounce: 0.32 },
    },
    tap: {
        scale: 0.96,
        transition: { type: 'spring', duration: 0.45, bounce: 0.25 },
    },
} as const;

</script>
