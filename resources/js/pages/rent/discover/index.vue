<template>
    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <section class="bg-white pb-12 pt-6">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-8 px-4 sm:px-6 lg:px-8">
                <DiscoverHeader
                    :is-category-page="isCategoryPage"
                    :category-name="props.category?.name ?? null"
                    :heading-text="headingText"
                    :sub-heading-text="subHeadingText"
                    v-model:search-term="searchTerm"
                    @search="submitSearch"
                />

                <DiscoverFilters
                    :category-options="categoryOptions"
                    :tag-options="tagOptions"
                    :selected-tags="selectedTags"
                    :selected-tag-items="selectedTagItems"
                    :has-active-filters="hasActiveFilters"
                    @toggle-tag="toggleTag"
                    @reset="resetFilters"
                    @remove-tag="removeTag"
                />

                <DiscoverProductGrid :products="displayProducts" />

                <DiscoverPagination
                    v-if="pagination"
                    :pagination="pagination"
                    @change="changePage"
                />
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import DiscoverFilters from './components/DiscoverFilters.vue';
import DiscoverHeader from './components/DiscoverHeader.vue';
import DiscoverPagination from './components/DiscoverPagination.vue';
import DiscoverProductGrid from './components/DiscoverProductGrid.vue';
import { formatPrice } from '@/lib/helper';

import type { Category, RentProduct, Tag } from '@/pages/rent/types';

type ResourceCollection<T> = { data?: T[] | null };

export interface DiscoverPageProps {
    rentProducts: Paginated<RentProduct>;
    categories?: ResourceCollection<Category> | Paginated<Category> | Category[];
    tags?: ResourceCollection<Tag> | Paginated<Tag> | Tag[];
    category?: Category | null;
    filters?: {
        q?: string | null;
        tags?: string[] | null;
        tag?: string | null;
    };
}

const props = withDefaults(defineProps<DiscoverPageProps>(), {
    categories: undefined,
    tags: undefined,
    category: null,
    filters: () => ({}),
});

const searchTerm = ref(props.filters?.q ?? '');
const initialTags = (() => {
    const provided = props.filters?.tags ?? [];
    if (Array.isArray(provided) && provided.length) {
        return [...provided];
    }
    if (props.filters?.tag) {
        return [props.filters.tag];
    }
    return [];
})();
const selectedTags = ref<string[]>(initialTags);

watch(
    () => props.filters?.q,
    (next) => {
        searchTerm.value = next ?? '';
    }
);

watch(
    () => props.filters?.tags,
    (next) => {
        if (Array.isArray(next)) {
            selectedTags.value = [...next];
        } else {
            selectedTags.value = [];
        }
    },
    { deep: true }
);

watch(
    () => props.filters?.tag,
    (next) => {
        if (!props.filters?.tags?.length) {
            selectedTags.value = next ? [next] : [];
        }
    }
);

const isCategoryPage = computed(() => Boolean(props.category));

const pageTitle = computed(() => {
    if (!props.category) return 'Khám phá vật tư sự kiện';
    return `${props.category.name} - Thuê vật tư Sukientot`;
});

const headingText = computed(() => (props.category ? props.category.name : 'Khám phá kho vật tư sự kiện'));

const totalItems = computed(() => props.rentProducts?.meta?.total ?? props.rentProducts?.data?.length ?? 0);

const subHeadingText = computed(() => {
    if (props.category) {
        return `${totalItems.value} vật tư trong danh mục này.`;
    }
    return `${totalItems.value} vật tư được đội ngũ Sukientot tuyển chọn.`;
});

function toArray<T>(input: ResourceCollection<T> | Paginated<T> | T[] | undefined): T[] {
    if (!input) return [];
    if (Array.isArray(input)) return input;
    if ('data' in input) {
        const data = input.data;
        return Array.isArray(data) ? data : [];
    }
    return [];
}

const tagOptions = computed(() => toArray<Tag>(props.tags));
const categoryOptions = computed(() => toArray<Category>(props.categories));
const selectedTagItems = computed(() => {
    const lookup = new Map(tagOptions.value.map((tag) => [tag.slug, tag]));
    return selectedTags.value.map((slug) => {
        const match = lookup.get(slug);
        return {
            slug,
            name: match?.name ?? slug,
        };
    });
});

const pagination = computed(() => props.rentProducts?.meta ?? null);

const displayProducts = computed(() =>
    (props.rentProducts?.data ?? []).map((item) => {
        const card = {
            id: item.id,
            name: item.name,
            slug: item.slug,
            image: item.image,
            description: item.description,
        };

        const hasCategory = Boolean(item.category?.slug);
        const href = hasCategory
            ? route('rent.show', {
                  category_slug: item.category?.slug,
                  rent_product_slug: item.slug,
              })
            : '#';

        const priceNumber = Number(item.price);
        const priceText = Number.isFinite(priceNumber) ? `${formatPrice(priceNumber)} đ` : 'Liên hệ';

        return {
            id: item.id,
            name: item.name,
            description: item.description,
            categoryName: item.category?.name ?? null,
            priceText,
            href,
            hasValidRoute: hasCategory,
            card,
        };
    })
);

const hasActiveFilters = computed(() => Boolean(searchTerm.value.trim() || selectedTags.value.length));

function buildQuery(extra: Partial<{ page: number }> = {}) {
    const query: Record<string, unknown> = { tag: null };
    const trimmed = searchTerm.value.trim();
    if (trimmed.length) query.q = trimmed;
    if (selectedTags.value.length) query.tags = [...selectedTags.value];
    if (extra.page && extra.page > 1) query.page = extra.page;
    return query;
}

function visitWithFilters(extra: Partial<{ page: number }> = {}) {
    const query = buildQuery(extra);
    const url = props.category?.slug
        ? route('rent.category', { category_slug: props.category.slug })
        : route('rent.discover');

    router.get(url, query, {
        preserveScroll: true,
        preserveState: true,
        replace: true,
    });
}

function submitSearch(value?: string) {
    if (typeof value === 'string') {
        searchTerm.value = value;
    }
    visitWithFilters();
}

function toggleTag(tagSlug: string | null | undefined) {
    if (!tagSlug) return;
    const index = selectedTags.value.findIndex((slug) => slug === tagSlug);
    if (index >= 0) {
        selectedTags.value.splice(index, 1);
    } else {
        selectedTags.value.push(tagSlug);
    }
    visitWithFilters();
}

function resetFilters() {
    searchTerm.value = '';
    selectedTags.value = [];
    visitWithFilters();
}

function removeTag(tagSlug: string) {
    const index = selectedTags.value.findIndex((slug) => slug === tagSlug);
    if (index < 0) return;
    selectedTags.value.splice(index, 1);
    visitWithFilters();
}

function changePage(page: number) {
    if (!pagination.value) return;
    if (page < 1 || page > pagination.value.last_page || page === pagination.value.current_page) return;
    visitWithFilters({ page });
}

</script>
