<template>
    <motion.div
        class="h-full"
        :initial="cardMotion.initial"
        :while-in-view="cardMotion.visible"
        :viewport="cardMotion.viewport"
        :transition="cardMotion.transition"
        :while-hover="cardInteractions.hover"
        :while-tap="cardInteractions.tap"
    >
        <Link :href="detailHref" class="h-full group relative block overflow-hidden rounded-3xl" :class="variantClass">
        <div class="absolute inset-0">
            <img
                v-if="blog.thumbnail"
                :src="getImg(blog.thumbnail)"
                :alt="blog.title"
                class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
            />
            <div
                v-else
                class="h-full w-full bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 transition duration-500 group-hover:from-slate-600 group-hover:to-slate-800"
            />
        </div>

        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent transition duration-500 group-hover:from-black/70" />

        <div class="relative flex h-full flex-col justify-end gap-3 p-6 text-white">
            <p v-if="dateLabel" class="text-xs font-semibold uppercase tracking-wider text-white/70">
                {{ dateLabel }}
            </p>
            <h3 :class="titleClass">
                {{ blog.title }}
            </h3>
            <p v-if="blog.excerpt" :class="excerptClass">
                {{ blog.excerpt }}
            </p>
            <div class="flex items-center gap-2 text-sm font-medium text-primary-200">
                <span>Đọc thêm</span>
                <span aria-hidden="true" class="transition-transform duration-300 group-hover:translate-x-1">→</span>
            </div>
            </div>
        </Link>
    </motion.div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import type { BlogSummary } from '../../types';
import { getImg } from '@/pages/booking/helper';

type Variant = 'featured' | 'secondary' | 'compact';

const props = withDefaults(
    defineProps<{ blog: BlogSummary; variant?: Variant; detailRouteName?: string; fallbackRouteName?: string }>(),
    {
        variant: 'compact',
        detailRouteName: 'blog.show',
        fallbackRouteName: 'blog.discover',
    }
);

const detailHref = computed(() => {
    const categorySlug = props.blog.category?.slug;
    if (categorySlug) {
        return route(props.detailRouteName, {
            category_slug: categorySlug,
            blog_slug: props.blog.slug,
        });
    }
    return route(props.fallbackRouteName);
});

const variantClass = computed(() => {
    switch (props.variant) {
        case 'featured':
            return 'min-h-[340px] sm:min-h-[420px]';
        case 'secondary':
            return 'min-h-[240px]';
        default:
            return 'min-h-[220px]';
    }
});

const titleClass = computed(() => {
    switch (props.variant) {
        case 'featured':
            return 'text-2xl font-semibold leading-snug line-clamp-3';
        case 'secondary':
            return 'text-xl font-semibold leading-snug line-clamp-3';
        default:
            return 'text-lg font-semibold leading-snug line-clamp-3';
    }
});

const excerptClass = computed(() => (props.variant === 'featured' ? 'text-base text-white/80 line-clamp-3' : 'text-sm text-white/80 line-clamp-2'));

const dateLabel = computed(() => props.blog.published_human ?? props.blog.published_at ?? '');

const cardMotion = {
    initial: { opacity: 0.5, y: 36 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.35 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.02,
        transition: { type: 'spring', duration: 0.5, bounce: 0.35 },
    },
    tap: {
        scale: 0.98,
        transition: { type: 'spring', duration: 0.45, bounce: 0.25 },
    },
} as const;
</script>
