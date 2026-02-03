<template>
    <motion.div class="h-full" :initial="cardMotion.initial" :while-in-view="cardMotion.visible"
        :viewport="cardMotion.viewport" :transition="cardMotion.transition">
        <Link :href="detailHref"
            class="group relative flex h-full flex-col overflow-hidden rounded-xl md:rounded-[2rem] bg-white shadow-sm transition-all duration-300 hover:shadow-xl cursor-pointer">
            <div class="relative aspect-[4/5] w-full overflow-hidden">
                <ImageWithLoader v-if="blog.thumbnail" :src="getImg(blog.thumbnail)" :alt="blog.title"
                    class="h-full w-full"
                    img-class="h-full w-full object-cover transition duration-700 group-hover:scale-110"
                    loading="lazy" />
                <div v-else class="flex h-full w-full items-center justify-center bg-slate-800 text-white">
                    <span class="text-lg font-medium">Không có hình ảnh</span>
                </div>

                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-60 transition-opacity duration-300 group-hover:opacity-70" />

                <div v-if="label" class="absolute right-1 md:right-4 top-0 md:top-4">
                    <span
                        class="rounded-lg bg-black/50 px-2 py-1 text-[8px] md:text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                        {{ label }}
                    </span>
                </div>

                <div class="absolute bottom-0 left-0 right-0 p-2 md:p-6 text-white">
                    <h3 class="mb-1 md:mb-3 line-clamp-2 text-sm md:text-xl font-bold leading-tight">
                        {{ blog.title }}
                    </h3>

                    <div v-if="showTags && blog.tags && blog.tags.length > 0" class="mb-4 flex flex-wrap gap-2">
                        <span v-for="tag in blog.tags" :key="tag.id ?? tag.slug ?? tag.name"
                            class="rounded-md bg-white/20 px-2 py-1 text-[10px] font-medium uppercase tracking-wide text-white backdrop-blur-sm">
                            {{ tag.name ?? tag.slug }}
                        </span>
                    </div>

                    <div v-else-if="showAddress && addressLabel"
                        class="mb-1 md:mb-4 flex gap-1 md:gap-2 text-xs text-white/90 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="w-4 h-4 mt-0.5 flex-shrink-0">
                            <path fill-rule="evenodd"
                                d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z"
                                clip-rule="evenodd" />
                        </svg>
                        <span class="line-clamp-2 text-[10px] md:text-sm">{{ addressLabel }}</span>
                    </div>

                    <div v-if="blog.max_people"
                        class="mb-1 md:mb-4 flex gap-1 md:gap-2 text-xs text-white/90 items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="w-4 h-4 mt-0.5 flex-shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 5.472m0 0a4.857 4.857 0 00-4.681-2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                        </svg>
                        <span class="line-clamp-2 text-[10px] md:text-sm">{{ blog.max_people }} khách</span>
                    </div>

                    <div class="flex items-center justify-between gap-0 md:gap-4">
                        <div v-if="blog.category?.name"
                            :class="['rounded-xl px-2 md:px-3 py-1 md:py-2 text-[8px] md:text-xs font-bold uppercase tracking-wide text-white', categoryBadgeClass]">
                            {{ blog.category.name }}
                        </div>

                        <div class="flex items-center gap-0 md:gap-2 text-[9px] md:text-sm font-medium text-white/80">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                                stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span v-if="blog.published_human">{{ blog.published_human }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </Link>
    </motion.div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import type { BlogSummary } from '../types';
import { getImg } from '@/pages/booking/helper';

import { inject } from "vue";

const route = inject('route') as any;

type Tone = 'primary' | 'blue' | 'red';

const props = withDefaults(
    defineProps<{
        blog: BlogSummary;
        label?: string;
        tone?: Tone;
        showTags?: boolean;
        showAddress?: boolean;
        detailRouteName?: string;
        fallbackRouteName?: string;
    }>(),
    {
        label: '',
        tone: 'primary',
        showTags: false,
        showAddress: false,
        detailRouteName: 'blog.show',
        fallbackRouteName: 'blog.discover',
    }
);

const badgeTones: Record<Tone, string> = {
    primary: 'bg-primary-500',
    blue: 'bg-blue-500',
    red: 'bg-red-500',
};

const categoryBadgeClass = computed(() => badgeTones[props.tone] ?? badgeTones.primary);

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

const addressLabel = computed(() => props.blog.address || props.blog.location?.name || '');

const cardMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;
</script>
