<template>
    <Head :title="pageTitle" />

<ClientHeaderLayout>
        <motion.section
            :initial="sectionMotion.initial"
            :animate="sectionMotion.animate"
            class="bg-white pb-12 pt-6">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
                <BlogDiscoverHeader
                    :is-category-page="isCategoryPage"
                    :category-name="props.category?.name ?? null"
                    :heading-text="headingText"
                    :sub-heading-text="subHeadingText"
                    :total-items="totalItems"
                    v-model:search-term="searchTerm"
                    @search="submitSearch"
                />

                <BlogCategoryFilters
                    :categories="categoryOptions"
                    :active-slug="activeCategorySlug"
                />

                <BlogGrid :blogs="props.blogs?.data ?? []" />

                <BlogPagination
                    v-if="pagination"
                    :pagination="pagination"
                    @change="changePage"
                />
            </div>
        </motion.section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { motion } from 'motion-v';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import BlogCategoryFilters from './components/BlogCategoryFilters.vue';
import BlogDiscoverHeader from './components/BlogDiscoverHeader.vue';
import BlogGrid from './components/BlogGrid.vue';
import BlogPagination from './components/BlogPagination.vue';

import type { BlogFilters, BlogSummary } from '../types';
import type { Category } from '@/pages/home/types';

type ResourceCollection<T> = { data?: T[] | null };

export interface DiscoverPageProps {
    blogs: Paginated<BlogSummary>;
    categories?: ResourceCollection<Category> | Paginated<Category> | Category[];
    category?: Category | null;
    filters?: BlogFilters;
}

const props = withDefaults(defineProps<DiscoverPageProps>(), {
    categories: undefined,
    category: null,
    filters: () => ({}),
});

const searchTerm = ref(props.filters?.q ?? '');

watch(
    () => props.filters?.q,
    (next) => {
        searchTerm.value = next ?? '';
    }
);

const isCategoryPage = computed(() => Boolean(props.category));
const activeCategorySlug = computed(() => props.category?.slug ?? null);

const pageTitle = computed(() => {
    if (!props.category) return 'Blog gợi ý địa điểm - Sukientot';
    return `${props.category.name} - Blog gợi ý địa điểm`;
});

const headingText = computed(() => (props.category ? props.category.name : 'Khám phá những địa điểm tổ chức sự kiện tốt nhất'));

const totalItems = computed(() => props.blogs?.meta?.total ?? props.blogs?.data?.length ?? 0);

const subHeadingText = computed(() => {
    if (props.category) {
        return `${totalItems.value} bài viết trong danh mục này.`;
    }
    return `${totalItems.value} gợi ý được tuyển chọn giúp bạn chuẩn bị sự kiện chu đáo.`;
});

const pagination = computed(() => props.blogs?.meta ?? null);

function toArray<T>(input: ResourceCollection<T> | Paginated<T> | T[] | undefined): T[] {
    if (!input) return [];
    if (Array.isArray(input)) return input;
    if ('data' in input) {
        const data = input.data;
        return Array.isArray(data) ? data : [];
    }
    return [];
}

const categoryOptions = computed(() => toArray<Category>(props.categories));

function submitSearch(term: string): void {
    const normalized = term.trim();
    const routeName = activeCategorySlug.value ? 'blog.category' : 'blog.discover';
    const routeParams = activeCategorySlug.value ? { category_slug: activeCategorySlug.value } : {};

    router.get(
        route(routeName, routeParams),
        {
            q: normalized !== '' ? normalized : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

function changePage(page: number): void {
    if (!pagination.value) return;

    const routeName = activeCategorySlug.value ? 'blog.category' : 'blog.discover';
    const routeParams = activeCategorySlug.value ? { category_slug: activeCategorySlug.value } : {};

    router.get(
        route(routeName, routeParams),
        {
            page,
            q: searchTerm.value.trim() !== '' ? searchTerm.value.trim() : undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        }
    );
}

const sectionMotion = {
    initial: {
        opacity: 0.5,
        y: 24,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.6,
            ease: 'easeOut',
        },
    },
} as const;

export type { DiscoverPageProps };
</script>
