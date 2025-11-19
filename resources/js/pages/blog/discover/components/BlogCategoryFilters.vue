<template>
    <motion.nav
        v-if="hasCategories"
        aria-label="Danh mục blog"
        class="flex flex-wrap items-center gap-3"
        :initial="navMotion.initial"
        :while-in-view="navMotion.visible"
        :viewport="navMotion.viewport"
        :transition="navMotion.transition"
    >
        <motion.div
            class="inline-flex"
            :initial="filterItemMotion.initial"
            :while-in-view="filterItemMotion.visible"
            :viewport="filterItemMotion.viewport"
            :transition="getStaggerTransition(0)"
            :while-hover="filterInteractions.hover"
            :while-tap="filterInteractions.tap"
        >
            <Link
                :href="route(allRouteName)"
                class="rounded-full border px-4 py-2 text-sm font-medium transition"
                :class="allActive ? 'border-primary-500 bg-primary-500 text-white shadow-sm' : 'border-gray-200 text-gray-700 hover:border-primary-400 hover:text-primary-600'"
                :aria-current="allActive ? 'page' : undefined"
            >
                Tất cả
            </Link>
        </motion.div>

        <motion.div
            v-for="(category, index) in categories"
            :key="category.id"
            class="inline-flex"
            :initial="filterItemMotion.initial"
            :while-in-view="filterItemMotion.visible"
            :viewport="filterItemMotion.viewport"
            :transition="getStaggerTransition(index + 1)"
            :while-hover="filterInteractions.hover"
            :while-tap="filterInteractions.tap"
        >
            <Link
                :href="route(categoryRouteName, { category_slug: category.slug })"
                class="rounded-full border px-4 py-2 text-sm font-medium transition"
                :class="category.slug === activeSlug ? 'border-primary-500 bg-primary-500 text-white shadow-sm' : 'border-gray-200 text-gray-700 hover:border-primary-400 hover:text-primary-600'"
                :aria-current="category.slug === activeSlug ? 'page' : undefined"
            >
                {{ category.name }}
            </Link>
        </motion.div>
    </motion.nav>
</template>

<script setup lang="ts">
import { computed, toRefs } from 'vue';
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import type { Category } from '@/pages/home/types';

const props = withDefaults(defineProps<{ categories: Category[]; activeSlug?: string | null; allRouteName?: string; categoryRouteName?: string }>(), {
    categories: () => [],
    activeSlug: null,
    allRouteName: 'blog.discover',
    categoryRouteName: 'blog.category',
});

const hasCategories = computed(() => props.categories.length > 0);
const allActive = computed(() => !props.activeSlug);

const { categories, activeSlug, allRouteName, categoryRouteName } = toRefs(props);

const navMotion = {
    initial: { opacity: 0.5, y: 16 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const filterItemMotion = {
    initial: { opacity: 0.5, y: 20 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.2 },
} as const;

const filterInteractions = {
    hover: {
        scale: 1.04,
        transition: { type: 'spring', duration: 0.5, bounce: 0.4 },
    },
    tap: {
        scale: 0.96,
        transition: { type: 'spring', duration: 0.45, bounce: 0.25 },
    },
} as const;

const getStaggerTransition = (index: number) => ({
    duration: 0.55,
    delay: Math.min(index * 0.05, 0.3),
    ease: 'easeOut',
});
</script>
