<template>

    <Head class="font-lexend" title="Thuê thiết bị sự kiện" />

    <ClientAppHeaderLayout :background-class-names="'bg-green-100'">
        <HeroBanner :banner-images="heroBannerImages">
            <HeroContentBlock
                tag-label="Thiết bị sự kiện"
                :title="heroHeaderText"
                :description="heroDescription"
                :primary-cta="{ label: 'Tìm thiết bị', href: '#search' }"
                :secondary-cta="{ label: 'Xem tất cả', href: route('rent.discover') }"
                :stats="[
                    { value: '3.2K+', label: 'Thiết bị sẵn sàng' },
                    { value: '15 phút', label: 'Phản hồi báo giá' },
                    { value: '360°', label: 'Hỗ trợ lắp đặt' }
                ]"
            />
        </HeroBanner>

        <PartnerCategoryIcons :categories="categories" />

        <div class="flex w-full flex-wrap justify-center gap-2 bg-white pt-3">
            <Link v-for="item in tags.data" :key="item.slug ?? item.id"
                :href="route('rent.discover', { q: item.slug })">
            <Button v-text="item.name" :size="'sm'" :variant="'outline'"
                :class="'bg-primary-10 text-primary-800 ring ring-primary-100 hover:bg-primary-50'" />
            </Link>
        </div>
        <div id="search" class="container-fluid p-2 sm:p-4 md:p-12 space-y-12 w-full max-w-7xl bg-white/10 backdrop-blur-lg border border-white/20 rounded-xl scroll-mt-24">
            <div class="max-w-5xl mx-auto">
                <SearchBar :show-search-btn="false" v-model="search" />
                <div v-if="keywordSuggestions.length" class="container mx-auto px-4 mt-4">
                    <div class="flex flex-wrap gap-2">
                        <button v-for="suggestion in keywordSuggestions" :key="suggestion" type="button"
                            class="rounded-full border border-primary-200 bg-white px-3 py-1 text-xs font-medium text-primary-700 hover:bg-primary-50 transition-colors"
                            @click="search = suggestion">
                            {{ suggestion }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <CardListLayout :href="route('rent.discover')" :name="'Thiết bị nổi bật gần đây'" :show-section="true"
            :items="rentProductList" @reach-end="handleRentReachEnd">
            <template #default="{ item }">
                <CardItem :key="item.id"
                    :route-href="route('rent.show', { rent_product_slug: item.slug, category_slug: item.category.slug })"
                    :card-item="item || []" />
            </template>
        </CardListLayout>

        <div class="flex w-full flex-wrap justify-center gap-2 bg-white pb-6">
            <Link :href="route('rent.discover')">
            <Button :size="'default'" :variant="'outline'" :class="'hover:bg-primary-50'"> Xem thêm thiết bị khác
            </Button>
            </Link>
        </div>
    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import CardListLayout from './layouts/CardListLayout.vue';

import HeroBanner from './partials/HeroBanner.vue';
import HeroContentBlock from './components/HeroContentBlock.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';

import type { PartnerCategoryItems } from './partials/PartnerCategoryIcons/type';
import type { Category, RentCardItemProps, RentProduct, Tag } from './types';

import { normText } from '@/lib/search-filter';
import { useSearchSuggestion } from '@/lib/useSearchSuggestion';

import { Button } from '@/components/ui/button';
import CardItem from './components/CardItem/index.vue';
import SearchBar from '../categories/partials/SearchBar.vue';
import { inject } from "vue";

const route = inject('route') as any;

interface BannerImage {
    image_tag?: string | null;
}

interface BannerImageWrapper {
    data: BannerImage[];
}

interface Props {
    rentProducts: Paginated<RentProduct>;
    tags: Paginated<Tag>;
    categories: Paginated<Category>;
    settings: {
        app_name: string;
        banner_images: BannerImageWrapper;
        hero_title?: string | null;
    };
}

const props = defineProps<Props>();

const {
    query: search,
    filteredLocal,
    setItems,
} = useSearchSuggestion<RentProduct>({
    keys: ['name', 'slug'],
    initialItems: props.rentProducts.data ?? [],
});

watch(
    () => props.rentProducts.data,
    (val) => setItems(val ?? [])
);

const heroBannerImages = computed(() => props.settings.banner_images.data ?? []);
const heroHeaderText = computed(() => 'Thuê thiết bị, loa đài ánh sáng');
const heroDescription = computed(
    () =>
        props.settings.hero_title ?? 'Đặt loa, màn hình, ánh sáng, sân khấu và nhân sự kỹ thuật chỉ với vài thao tác. Nhận hỗ trợ vận hành trọn gói cho sự kiện của bạn.',
);

const filteredRentProducts = computed(() => filteredLocal.value ?? []);

const categories = computed<PartnerCategoryItems[]>(() =>
    (props.categories.data ?? []).map((item) => ({
        id: item.id,
        name: item.name,
        slug: item.slug,
        icon: '',
        image: item.image ?? '',
        href: route('rent.category', { category_slug: item.slug }),
    })),
);

const rentProductList = computed<RentCardItemProps[]>(() =>
    (filteredRentProducts.value ?? []).map((item) => ({
        id: item.id,
        name: item.name,
        slug: item.slug,
        image: item.image ?? null,
        description: item.description ?? '',
        category: item.category,
    })),
);

const handleRentReachEnd = () => {
    // Hook for lazy loading more rent products when the slider hits the end.
};

const keywordSuggestions = computed(() => {
    const term = search.value.trim();
    if (term.length < 2) return [];

    const names = new Set<string>();
    filteredRentProducts.value.forEach((item) => {
        if (item?.name) names.add(item.name);
    });

    const normalizedTerm = normText(term);
    return Array.from(names)
        .filter((name) => normText(name).includes(normalizedTerm))
        .slice(0, 8);
});
</script>
