<template>
    <Head class="font-lexend" title="Kho tài liệu thiết kế - Sukientot" />

    <ClientAppHeaderLayout :background-class-names="'bg-blue-100'">

        <HeroBanner :header-text="'Khám phá kho tài liệu thiết kế mới'" v-model="search" 
            :bg-color-class="'bg-[linear-gradient(180deg,#6bafff_0%,rgb(129,187,255)_51.50151983037725%,rgba(74,144,226,0)_100%)]'"
        />

        <PartnerCategoryIcons :categories="categories" />

        <div class="w-full pt-3 bg-white flex gap-2 justify-center flex-wrap">
            <Link v-for="item in tags.data" :href="route('asset.discover', { q: item.slug })">
                <Button v-text="item.name" :size="'sm'" :variant="'outline'" :class="'ring ring-primary-100 text-primary-800 bg-primary-10 hover:bg-primary-50'"></Button>
            </Link>
        </div>

        <CardListLayout :href="route('asset.discover')" :name="'Tài liệu mới gần đây'" :show-section="true">
            <CardItem v-for="item in fileProductList"
                :route-href="route('asset.show', { file_product_slug: item.slug, category_slug: item.category.slug })"
                :key="item.id" :card-item="item || []" />
        </CardListLayout>

        <div class="w-full pb-6 bg-white flex gap-2 justify-center flex-wrap">
            <Link :href="route('asset.discover')">
                <Button :size="'default'" :variant="'outline'" :class="'hover:bg-primary-50'">Xem thêm sản phẩm khác</Button>
            </Link>
        </div>

    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import CardListLayout from './layouts/CardListLayout.vue';

import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';

import { PartnerCategoryItems } from './partials/PartnerCategoryIcons/type';
import { AssetCardItemProps, Category, FileProduct, Tag } from './types';

import { createSearchFilter } from '@/lib/search-filter';

import CardItem from './components/CardItem/index.vue';
import { Button } from '@/components/ui/button';

interface Props {
    fileProducts: Paginated<FileProduct>;
    tags: Paginated<Tag>;
    categories: Paginated<Category>;
}

const props = defineProps<Props>();

const search = ref('');

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

const fileProductList = computed<AssetCardItemProps[]>(() =>
    (filteredFileProducts.value ?? []).map((item) => {
        return {
            id: item.id,
            name: item.name,
            slug: item.slug,
            image: item.image ?? null,
            category: item.category
        }
    })
);

</script>