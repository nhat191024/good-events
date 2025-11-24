<template>
    <motion.div class="h-full" :initial="cardMotion.initial" :while-in-view="cardMotion.visible"
        :viewport="cardMotion.viewport" :transition="cardMotion.transition">
        <Link
            :href="route(detailRouteName, { category_slug: blog.category?.slug ?? 'uncategorized', blog_slug: blog.slug })"
            class="group relative flex h-full flex-col overflow-hidden rounded-[2rem] bg-white shadow-sm transition-all duration-300 hover:shadow-xl cursor-pointer">
        <!-- Thumbnail Section -->
        <div class="relative aspect-[4/5] w-full overflow-hidden">
            <img v-if="blog.thumbnail" :src="getImg(blog.thumbnail)" :alt="blog.title"
                class="h-full w-full object-cover transition duration-700 group-hover:scale-110" />
            <div v-else class="flex h-full w-full items-center justify-center bg-slate-100 text-slate-400">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="w-12 h-12">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                </svg>
            </div>

            <!-- Overlay Gradient -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-60 transition-opacity duration-300 group-hover:opacity-70">
            </div>

            <div class="absolute right-4 top-4">
                <span
                    class="rounded-lg bg-black/50 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                    Hướng dẫn
                </span>
            </div>

            <!-- Content Overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                <h3 class="mb-3 line-clamp-2 text-xl font-bold leading-tight">
                    {{ blog.title }}
                </h3>

                <div class="flex items-center justify-between gap-4">
                    <!-- Category Badge -->
                    <div v-if="blog.category?.name"
                        class="rounded-xl bg-blue-500 px-3 py-2 text-xs font-bold uppercase tracking-wide text-white">
                        {{ blog.category.name }}
                    </div>

                    <div class="flex items-center gap-2 text-xs font-medium text-white/80">
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
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import type { BlogSummary } from '../../types';
import { getImg } from '@/pages/booking/helper';

const props = withDefaults(defineProps<{
    blog: BlogSummary;
    detailRouteName?: string;
}>(), {
    detailRouteName: 'blog.guides.show'
});

const cardMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;
</script>
