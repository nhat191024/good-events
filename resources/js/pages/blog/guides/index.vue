<template>

    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <motion.section :initial="sectionMotion.initial" :animate="sectionMotion.animate"
            class="bg-white w-full pb-12 pt-6 w-full">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 md:gap-10 px-2 md:px-4 sm:px-6 lg:px-8">
                <BlogDiscoverHeader :is-category-page="isCategoryPage" :category-name="props.category?.name ?? null"
                    :heading-text="headingText" :sub-heading-text="subHeadingText" :total-items="totalItems"
                    breadcrumb-label="Blog hướng dẫn" breadcrumb-route-name="blog.guides.discover"
                    search-placeholder="Tìm kiếm hướng dẫn tổ chức sự kiện..." v-model:search-term="searchTerm"
                    @search="submitSearch" />

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

                <BlogCategoryFilters :categories="categoryOptions" :active-slug="activeCategorySlug"
                    all-route-name="blog.guides.discover" category-route-name="blog.guides.category" />

                <section>
                    <div v-if="displayedBlogs.length" class="grid gap-2 grid-cols-2 lg:grid-cols-3">
                        <BlogHighlightCard
                            v-for="blog in displayedBlogs"
                            :key="blog.id"
                            :blog="blog"
                            label="Hướng dẫn"
                            tone="blue"
                            detail-route-name="blog.guides.show"
                            fallback-route-name="blog.guides.discover"
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
import BlogDiscoverHeader from '../discover/components/BlogDiscoverHeader.vue';
import BlogCategoryFilters from '../discover/components/BlogCategoryFilters.vue';
import BlogHighlightCard from '../components/BlogHighlightCard.vue';
import BlogPagination from '../discover/components/BlogPagination.vue';

import { createSearchFilter, normText } from '@/lib/search-filter';

import type { BlogFilters, BlogSummary } from '../types';
import type { Category } from '@/pages/home/types';
import { inject } from "vue";

const route = inject('route') as any;

type ResourceCollection<T> = { data?: T[] | null };

export interface GuidePageProps {
    blogs: Paginated<BlogSummary>;
    categories?: ResourceCollection<Category> | Paginated<Category> | Category[];
    category?: Category | null;
    filters?: BlogFilters;
}

const props = withDefaults(defineProps<GuidePageProps>(), {
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

    const filter = createSearchFilter<BlogSummary>(['title', 'slug', 'excerpt', 'category.name', 'tags.name'], query);
    return items.filter(filter);
});

const totalItems = computed(() => {
    if (isLiveSearch.value) return displayedBlogs.value.length;
    return props.blogs?.meta?.total ?? props.blogs?.data?.length ?? 0;
});

const pageTitle = computed(() => {
    if (props.category) return `${props.category.name} - Hướng dẫn tổ chức sự kiện`;
    return 'Hướng dẫn tổ chức sự kiện';
});

const headingText = computed(() => (props.category ? props.category.name : 'Hướng dẫn tổ chức sự kiện'));

const subHeadingText = computed(() => {
    if (props.category) {
        return `${totalItems.value} bài hướng dẫn trong danh mục này.`;
    }
    return 'Những bí quyết tổ chức sự kiện hiệu quả trên sukientot.com.';
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
    const routeName = activeCategorySlug.value ? 'blog.guides.category' : 'blog.guides.discover';
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

    const routeName = activeCategorySlug.value ? 'blog.guides.category' : 'blog.guides.discover';
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

const keywordSuggestions = computed(() => {
    const term = normalizedSearchTerm.value;
    if (term.length < 2) return [];
    const normalizedTerm = normText(term);
    const seen = new Set<string>();
    const items: { label: string; type: 'tag' | 'keyword'; slug?: string }[] = [];

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
    submitSearch(suggestion.label);
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

export type { GuidePageProps };
</script>
