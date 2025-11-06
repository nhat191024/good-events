<template>
    <Link :href="detailHref" class="group relative block overflow-hidden rounded-3xl" :class="variantClass">
        <div class="absolute inset-0">
            <img
                v-if="blog.thumbnail"
                :src="blog.thumbnail"
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
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { BlogSummary } from '../../types';

type Variant = 'featured' | 'secondary' | 'compact';

const props = withDefaults(defineProps<{ blog: BlogSummary; variant?: Variant }>(), {
    variant: 'compact',
});

const detailHref = computed(() => {
    const categorySlug = props.blog.category?.slug;
    if (categorySlug) {
        return route('blog.show', {
            category_slug: categorySlug,
            blog_slug: props.blog.slug,
        });
    }
    return route('blog.discover');
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
</script>

