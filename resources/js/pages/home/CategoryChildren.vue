<template>
    <Head class="font-lexend" :title="category.name" />

    <ClientAppHeaderLayout :background-class-names="'bg-primary-100'">
        <section class="container mx-auto px-4 py-10 space-y-6">
            <div class="space-y-2">
                <p class="text-sm font-semibold uppercase tracking-wide text-primary-600">Danh mục sự kiện</p>
                <h1 class="text-3xl font-bold text-gray-900">{{ category.name }}</h1>
                <div v-if="category.description" class="text-gray-600" v-html="category.description">
                </div>
                <p class="text-sm text-gray-500">Tổng {{ category.total_children }} danh mục con</p>
            </div>

            <div class="max-w-xl">
                <label class="sr-only" for="child-search">Tìm kiếm danh mục con</label>
                <input
                    id="child-search"
                    v-model="search"
                    type="search"
                    placeholder="Tìm kiếm danh mục con..."
                    class="w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:border-primary-500 focus:outline-none focus:ring-1 focus:ring-primary-500"
                />
            </div>

            <div v-if="filteredCategories.length" class="grid grid-cols-2 gap-4 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <PartnerCategoryCard v-for="partnerCategory in filteredCategories" :key="partnerCategory.id"
                    :partner-category="partnerCategory" />
            </div>
            <p v-else class="text-center text-gray-500">Không tìm thấy danh mục con phù hợp.</p>
        </section>
    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import PartnerCategoryCard from './partials/PartnerCategoryCard.vue';
import type { PartnerCategory } from '@/types/database';
import { createSearchFilter } from '@/lib/search-filter';

interface CategorySummary {
    id: number;
    name: string;
    slug: string;
    description?: string | null;
    image?: string | null;
    total_children: number;
}

interface Props {
    category: CategorySummary;
    partnerCategories: PartnerCategory[];
    settings: {
        app_name: string;
        hero_title?: string | null;
    };
}

const props = defineProps<Props>();

const search = ref('');

const filteredCategories = computed(() => {
    const q = search.value.trim();
    if (!q) return props.partnerCategories;
    const filter = createSearchFilter(['name', 'slug'], q);
    return props.partnerCategories.filter(filter);
});
</script>
