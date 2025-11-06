<template>
    <nav v-if="hasCategories" aria-label="Danh mục blog" class="flex flex-wrap items-center gap-3">
        <Link
            :href="route('blog.discover')"
            class="rounded-full border px-4 py-2 text-sm font-medium transition"
            :class="allActive ? 'border-primary-500 bg-primary-500 text-white shadow-sm' : 'border-gray-200 text-gray-700 hover:border-primary-400 hover:text-primary-600'"
            :aria-current="allActive ? 'page' : undefined"
        >
            Tất cả
        </Link>

        <Link
            v-for="category in categories"
            :key="category.id"
            :href="route('blog.category', { category_slug: category.slug })"
            class="rounded-full border px-4 py-2 text-sm font-medium transition"
            :class="category.slug === activeSlug ? 'border-primary-500 bg-primary-500 text-white shadow-sm' : 'border-gray-200 text-gray-700 hover:border-primary-400 hover:text-primary-600'"
            :aria-current="category.slug === activeSlug ? 'page' : undefined"
        >
            {{ category.name }}
        </Link>
    </nav>
</template>

<script setup lang="ts">
import { computed, toRefs } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { Category } from '@/pages/home/types';

const props = withDefaults(defineProps<{ categories: Category[]; activeSlug?: string | null }>(), {
    categories: () => [],
    activeSlug: null,
});

const hasCategories = computed(() => props.categories.length > 0);
const allActive = computed(() => !props.activeSlug);

const { categories, activeSlug } = toRefs(props);
</script>
