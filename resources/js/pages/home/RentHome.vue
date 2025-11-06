<template>
    <Head class="font-lexend" title="Thuê vật tư sự kiện - Sukientot" />

    <ClientAppHeaderLayout :background-class-names="'bg-green-100'">

        <HeroBanner
            :header-text="'Giải pháp thuê vật tư sự kiện nhanh chóng'"
            v-model="search"
            :bg-color-class="'bg-[linear-gradient(180deg,#4ade80_0%,rgb(134,239,172)_51.5%,rgba(74,222,128,0)_100%)]'"
        />

        <PartnerCategoryIcons :categories="categories" />

        <div class="w-full pt-3 bg-white flex gap-2 justify-center flex-wrap">
            <Link v-for="item in tags.data" :key="item.slug ?? item.id" :href="route('rent.discover', { q: item.slug })">
                <Button
                    v-text="item.name"
                    :size="'sm'"
                    :variant="'outline'"
                    :class="'ring ring-primary-100 text-primary-800 bg-primary-10 hover:bg-primary-50'"
                />
            </Link>
        </div>

        <CardListLayout :href="route('rent.discover')" :name="'Vật tư nổi bật gần đây'" :show-section="true">
            <CardItem
                v-for="item in rentProductList"
                :key="item.id"
                :route-href="route('rent.show', { rent_product_slug: item.slug, category_slug: item.category.slug })"
                :card-item="item || []"
            />
        </CardListLayout>

        <div class="w-full pb-6 bg-white flex gap-2 justify-center flex-wrap">
            <Link :href="route('rent.discover')">
                <Button :size="'default'" :variant="'outline'" :class="'hover:bg-primary-50'">
                    Xem thêm vật tư khác
                </Button>
            </Link>
        </div>

    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import CardListLayout from './layouts/CardListLayout.vue';

import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';

import type { PartnerCategoryItems } from './partials/PartnerCategoryIcons/type';
import type { RentCardItemProps, Category, RentProduct, Tag } from './types';

import { createSearchFilter } from '@/lib/search-filter';

import CardItem from './components/CardItem/index.vue';
import { Button } from '@/components/ui/button';

interface Props {
    rentProducts: Paginated<RentProduct>;
    tags: Paginated<Tag>;
    categories: Paginated<Category>;
}

const props = defineProps<Props>();

const search = ref('');

const filteredRentProducts = computed(() => {
    const data = props.rentProducts.data ?? [];
    const q = search.value.trim();
    if (!q) return data;

    const filter = createSearchFilter<RentProduct>(['name', 'slug'], q);
    return data.filter(filter);
});

const categories = computed<PartnerCategoryItems[]>(() =>
    (props.categories.data ?? []).map((item) => ({
        id: item.id,
        name: item.name,
        slug: item.slug,
        icon: '',
        image: item.image ?? '',
        href: route('rent.category', { category_slug: item.slug }),
    }))
);

const rentProductList = computed<RentCardItemProps[]>(() =>
    (filteredRentProducts.value ?? []).map((item) => ({
        id: item.id,
        name: item.name,
        slug: item.slug,
        image: item.image ?? null,
        description: item.description ?? '',
        category: item.category,
    }))
);
</script>
