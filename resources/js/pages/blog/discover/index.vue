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
                    v-model:search-term="searchTerm"
                    @search="submitSearch"
                />

                <BlogLocationFilterBar
                    :provinces="provinceOptions"
                    :province-id="selectedProvinceId"
                    :districts="districtOptions"
                    :district-id="selectedDistrictId"
                    :loading="loadingWards"
                    @update:province-id="handleProvinceChange"
                    @update:district-id="handleDistrictChange"
                    @clear="clearLocationFilters"
                />

                <BlogCategoryFilters
                    :categories="categoryOptions"
                    :active-slug="activeCategorySlug"
                    all-route-name="blog.discover"
                    category-route-name="blog.category"
                />

                <BlogGrid :blogs="displayedBlogs" />

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
import BlogLocationFilterBar from './components/BlogLocationFilterBar.vue';

import { createSearchFilter } from '@/lib/search-filter';
import { useWards } from '@/helper/useWards';

import type { BlogFilters, BlogSummary } from '../types';
import type { Category } from '@/pages/home/types';
import type { Province } from '@/types/database';

type ResourceCollection<T> = { data?: T[] | null };

export interface DiscoverPageProps {
    blogs: Paginated<BlogSummary>;
    categories?: ResourceCollection<Category> | Paginated<Category> | Category[];
    category?: Category | null;
    filters?: BlogFilters;
    locations?: {
        provinces?: Province[];
    };
}

const props = withDefaults(defineProps<DiscoverPageProps>(), {
    categories: undefined,
    category: null,
    filters: () => ({}),
    locations: () => ({
        provinces: [],
    }),
});

const searchTerm = ref(props.filters?.q ?? '');
const selectedProvinceId = ref<string | null>(props.filters?.province_id ? String(props.filters?.province_id) : null);
const selectedDistrictId = ref<string | null>(props.filters?.district_id ? String(props.filters?.district_id) : null);

watch(
    () => props.filters?.q,
    (next) => {
        searchTerm.value = next ?? '';
    }
);

watch(
    () => props.filters?.province_id,
    (next) => {
        selectedProvinceId.value = next ? String(next) : null;
    },
    { immediate: true }
);

watch(
    () => props.filters?.district_id,
    (next) => {
        selectedDistrictId.value = next ? String(next) : null;
    },
    { immediate: true }
);

const { provinceId: wardsProvinceId, wardList, loadingWards } = useWards({ auto: true });

watch(
    () => selectedProvinceId.value,
    (next, prev) => {
        if (next !== prev) {
            selectedDistrictId.value = null;
        }
        wardsProvinceId.value = next;
    },
    { immediate: true }
);

const isCategoryPage = computed(() => Boolean(props.category));
const activeCategorySlug = computed(() => props.category?.slug ?? null);

const pageTitle = computed(() => {
    if (!props.category) return 'Blog gợi ý địa điểm';
    return `${props.category.name} - Blog gợi ý địa điểm`;
});

const headingText = computed(() => (props.category ? props.category.name : 'Khám phá những địa điểm tổ chức sự kiện tốt nhất'));

const normalizedSearchTerm = computed(() => searchTerm.value.trim());
const normalizedServerQuery = computed(() => (props.filters?.q ?? '').trim());
const isLiveSearch = computed(
    () => normalizedSearchTerm.value !== '' && normalizedSearchTerm.value !== normalizedServerQuery.value
);

const displayedBlogs = computed(() => {
    const items = props.blogs?.data ?? [];
    const query = normalizedSearchTerm.value;
    if (!query) return items;

    const filter = createSearchFilter<BlogSummary>(['title', 'slug', 'excerpt', 'category.name'], query);
    return items.filter(filter);
});

const totalItems = computed(() => {
    if (isLiveSearch.value) {
        return displayedBlogs.value.length;
    }
    return props.blogs?.meta?.total ?? props.blogs?.data?.length ?? 0;
});

const subHeadingText = computed(() => {
    if (props.category) {
        return `${totalItems.value} bài viết trong danh mục này.`;
    }
    return `${totalItems.value} gợi ý được tuyển chọn giúp bạn chuẩn bị sự kiện chu đáo.`;
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
const provinceOptions = computed<Province[]>(() => props.locations?.provinces ?? []);
const districtOptions = computed(() =>
    wardList.value.map((item) => ({
        name: item.name,
        value: item.value ?? '',
    }))
);

function buildQueryParams(additional: Record<string, unknown> = {}) {
    const base = {
        q: normalizedServerQuery.value !== '' ? normalizedServerQuery.value : undefined,
        province_id: selectedProvinceId.value ?? undefined,
        district_id: selectedDistrictId.value ?? undefined,
    };
    return { ...base, ...additional };
}

function submitSearch(term: string): void {
    const normalized = term.trim();
    const routeName = activeCategorySlug.value ? 'blog.category' : 'blog.discover';
    const routeParams = activeCategorySlug.value ? { category_slug: activeCategorySlug.value } : {};

    router.get(
        route(routeName, routeParams),
        {
            ...buildQueryParams({ q: normalized !== '' ? normalized : undefined }),
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

    router.get(route(routeName, routeParams), { ...buildQueryParams({ page }) }, { preserveState: true, preserveScroll: true });
}

function handleProvinceChange(value: string | null): void {
    selectedProvinceId.value = value;
    selectedDistrictId.value = null;
    applyLocationFilter();
}

function handleDistrictChange(value: string | null): void {
    selectedDistrictId.value = value;
    applyLocationFilter();
}

function clearLocationFilters(): void {
    if (!selectedProvinceId.value && !selectedDistrictId.value) {
        return;
    }
    selectedProvinceId.value = null;
    selectedDistrictId.value = null;
    applyLocationFilter();
}

function applyLocationFilter(): void {
    const routeName = activeCategorySlug.value ? 'blog.category' : 'blog.discover';
    const routeParams = activeCategorySlug.value ? { category_slug: activeCategorySlug.value } : {};

    router.get(route(routeName, routeParams), buildQueryParams(), {
        preserveState: true,
        preserveScroll: true,
    });
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
