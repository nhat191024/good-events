<template>
    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <motion.section
            :initial="sectionMotion.initial"
            :animate="sectionMotion.animate"
            class="bg-white pb-12 pt-6 w-full">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
                <BlogDiscoverHeader
                    :is-category-page="isCategoryPage"
                    :category-name="props.category?.name ?? null"
                    :heading-text="headingText"
                    :sub-heading-text="subHeadingText"
                    :total-items="totalItems"
                    breadcrumb-label="Kiến thức nghề"
                    breadcrumb-route-name="blog.knowledge.discover"
                    search-placeholder="Tìm video kiến thức nghề..."
                    v-model:search-term="searchTerm"
                    @search="submitSearch"
                />

                <BlogCategoryFilters
                    :categories="categoryOptions"
                    :active-slug="activeCategorySlug"
                    all-route-name="blog.knowledge.discover"
                    category-route-name="blog.knowledge.category"
                />

                <section>
                    <div v-if="displayedBlogs.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <VideoCard
                            v-for="blog in displayedBlogs"
                            :key="blog.id"
                            :blog="blog"
                        />
                    </div>
                    <div v-else class="rounded-3xl border border-dashed border-gray-200 bg-gray-50 py-16 text-center text-sm text-gray-500">
                        Hiện chưa có video nào.
                    </div>
                </section>

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
import BlogDiscoverHeader from '../discover/components/BlogDiscoverHeader.vue';
import BlogCategoryFilters from '../discover/components/BlogCategoryFilters.vue';
import BlogPagination from '../discover/components/BlogPagination.vue';
import VideoCard from './components/VideoCard.vue';

import { createSearchFilter } from '@/lib/search-filter';

import type { BlogFilters, BlogSummary } from '../types';
import type { Category } from '@/pages/home/types';

type ResourceCollection<T> = { data?: T[] | null };

export interface KnowledgePageProps {
    blogs: Paginated<BlogSummary>;
    categories?: ResourceCollection<Category> | Paginated<Category> | Category[];
    category?: Category | null;
    filters?: BlogFilters;
}

const props = withDefaults(defineProps<KnowledgePageProps>(), {
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

const normalizedSearchTerm = computed(() => searchTerm.value.trim());
const normalizedServerQuery = computed(() => (props.filters?.q ?? '').trim());
const isLiveSearch = computed(
    () => normalizedSearchTerm.value !== '' && normalizedSearchTerm.value !== normalizedServerQuery.value
);

const displayedBlogs = computed(() => {
    const items = props.blogs?.data ?? [];
    const query = normalizedSearchTerm.value;
    if (!query) return items;

    const filter = createSearchFilter<BlogSummary>(['title', 'slug', 'excerpt', 'category.name', 'video_url'], query);
    return items.filter(filter);
});

const totalItems = computed(() => {
    if (isLiveSearch.value) return displayedBlogs.value.length;
    return props.blogs?.meta?.total ?? props.blogs?.data?.length ?? 0;
});

const pageTitle = computed(() => {
    if (props.category) return `${props.category.name} - Kiến thức nghề`;
    return 'Kiến thức nghề';
});

const headingText = computed(() => (props.category ? props.category.name : 'Kho video kiến thức nghề'));

const subHeadingText = computed(() => {
    if (props.category) {
        return `${totalItems.value} video trong danh mục này.`;
    }
    return 'Chia sẻ kiến thức thực tế giúp bạn nâng cao chuyên môn tổ chức sự kiện.';
});

const pagination = computed(() => (isLiveSearch.value ? null : props.blogs?.meta ?? null));

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
    const routeName = activeCategorySlug.value ? 'blog.knowledge.category' : 'blog.knowledge.discover';
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

    const routeName = activeCategorySlug.value ? 'blog.knowledge.category' : 'blog.knowledge.discover';
    const routeParams = activeCategorySlug.value ? { category_slug: activeCategorySlug.value } : {};

    router.get(
        route(routeName, routeParams),
        {
            page,
            q: normalizedServerQuery.value !== '' ? normalizedServerQuery.value : undefined,
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

export type { KnowledgePageProps };
</script>
