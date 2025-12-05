<template>

    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <motion.section :initial="sectionMotion.initial" :animate="sectionMotion.animate"
            class="bg-white w-full pb-12 pt-6 w-full">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
                <BlogDiscoverHeader :is-category-page="isCategoryPage" :category-name="props.category?.name ?? null"
                    :heading-text="headingText" :sub-heading-text="subHeadingText" :total-items="totalItems"
                    v-model:search-term="searchTerm" @search="submitSearch" />

                <div v-if="keywordSuggestions.length" class="flex flex-wrap gap-2">
                    <button
                        v-for="suggestion in keywordSuggestions"
                        :key="`${suggestion.type}-${suggestion.slug ?? suggestion.label}`"
                        type="button"
                        class="rounded-full border border-primary-200 bg-white px-3 py-1 text-xs font-medium text-primary-700 hover:bg-primary-50 transition-colors"
                        @click="applySuggestion(suggestion)"
                    >
                        <span v-if="suggestion.type === 'tag'" class="mr-1 text-[11px] uppercase text-primary-500">Tag</span>
                        {{ suggestion.label }}
                    </button>
                </div>

                <BlogLocationFilterBar :provinces="provinceOptions" :province-id="selectedProvinceId"
                    :districts="districtOptions" :district-id="selectedDistrictId" :max-people="selectedMaxPeople"
                    :location-detail="selectedLocationDetail" :loading="loadingWards"
                    @update:province-id="handleProvinceChange" @update:district-id="handleDistrictChange"
                    @update:max-people="handleMaxPeopleChange" @update:location-detail="handleLocationDetailChange"
                    @clear="clearLocationFilters" />

                <BlogCategoryFilters :categories="categoryOptions" :active-slug="activeCategorySlug"
                    all-route-name="blog.discover" category-route-name="blog.category" />

                <section>
                    <div v-if="displayedBlogs.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        <BlogHighlightCard
                            v-for="blog in displayedBlogs"
                            :key="blog.id"
                            :blog="blog"
                            label="Địa điểm"
                            tone="primary"
                            detail-route-name="blog.show"
                            fallback-route-name="blog.discover"
                            show-address
                        />
                    </div>
                    <div v-else
                        class="rounded-3xl border border-dashed border-gray-200 bg-gray-50 py-16 text-center text-sm text-gray-500">
                        Hiện chưa có bài viết nào.
                    </div>
                </section>

                <BlogPagination v-if="pagination" :pagination="pagination" @change="changePage" />
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
import BlogHighlightCard from '../components/BlogHighlightCard.vue';
import BlogPagination from './components/BlogPagination.vue';
import BlogLocationFilterBar from './components/BlogLocationFilterBar.vue';

import { createSearchFilter, normText } from '@/lib/search-filter';
import { useWards } from '@/helper/useWards';

import type { BlogFilters, BlogSummary } from '../types';
import type { Category } from '@/pages/home/types';
import type { Province } from '@/types/database';

type ResourceCollection<T> = { data?: T[] | null };

export interface DiscoverPageProps {
    blogs: Paginated<BlogSummary>;
    categories?: ResourceCollection<Category> | Paginated<Category> | Category[];
    category?: Category | null;
    filters?: BlogFilters & {
        max_people?: string;
        location_detail?: string;
    };
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
const selectedMaxPeople = ref<string | null>(props.filters?.max_people ? String(props.filters?.max_people) : null);
const selectedLocationDetail = ref<string | null>(props.filters?.location_detail ? String(props.filters?.location_detail) : null);

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

watch(
    () => props.filters?.max_people,
    (next) => {
        selectedMaxPeople.value = next ? String(next) : null;
    },
    { immediate: true }
);

watch(
    () => props.filters?.location_detail,
    (next) => {
        selectedLocationDetail.value = next ? String(next) : null;
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

    const filter = createSearchFilter<BlogSummary>(['title', 'slug', 'excerpt', 'category.name', 'tags.name'], query);
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
        max_people: selectedMaxPeople.value ?? undefined,
        location_detail: selectedLocationDetail.value ?? undefined,
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

const keywordSuggestions = computed(() => {
    const term = normalizedSearchTerm.value;
    if (term.length < 2) return [];
    const normalizedTerm = normText(term);
    const seen = new Set<string>();
    const items: { label: string; type: 'tag' | 'keyword'; slug?: string }[] = [];

    // Tags
    (props.blogs?.data ?? []).forEach((blog) => {
        blog.tags?.forEach((tag) => {
            const label = tag.name ?? tag.slug ?? '';
            if (!label) return;
            const key = `tag:${label}`;
            if (seen.has(key)) return;
            if (normText(label).includes(normalizedTerm)) {
                seen.add(key);
                items.push({ label, type: 'tag', slug: tag.slug ?? label });
            }
        });
    });

    // Titles / excerpts
    (props.blogs?.data ?? []).forEach((blog) => {
        const label = blog.title ?? '';
        if (label) {
            const key = `kw:${label}`;
            if (!seen.has(key) && normText(label).includes(normalizedTerm)) {
                seen.add(key);
                items.push({ label, type: 'keyword' });
            }
        }
        const excerpt = blog.excerpt ?? '';
        if (excerpt) {
            const key = `kw:${excerpt}`;
            if (!seen.has(key) && normText(excerpt).includes(normalizedTerm)) {
                seen.add(key);
                items.push({ label: excerpt.slice(0, 80) + (excerpt.length > 80 ? '…' : ''), type: 'keyword' });
            }
        }
    });

    return items.slice(0, 10);
});

function applySuggestion(suggestion: { label: string; type: 'tag' | 'keyword'; slug?: string }) {
    if (suggestion.type === 'tag') {
        submitSearch(suggestion.label);
        return;
    }
    submitSearch(suggestion.label);
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

function handleMaxPeopleChange(value: string | null): void {
    selectedMaxPeople.value = value;
    applyLocationFilter();
}

function handleLocationDetailChange(value: string | null): void {
    selectedLocationDetail.value = value;
    applyLocationFilter();
}

function clearLocationFilters(): void {
    if (!selectedProvinceId.value && !selectedDistrictId.value && !selectedMaxPeople.value && !selectedLocationDetail.value) {
        return;
    }
    selectedProvinceId.value = null;
    selectedDistrictId.value = null;
    selectedMaxPeople.value = null;
    selectedLocationDetail.value = null;
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
