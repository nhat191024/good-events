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
                        d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                </svg>
            </div>

            <!-- Overlay Gradient -->
            <div
                class="absolute inset-0 bg-gradient-to-t from-black/90 via-black/40 to-transparent opacity-60 transition-opacity duration-300 group-hover:opacity-70">
            </div>

            <div class="absolute right-4 top-4">
                <span
                    class="rounded-lg bg-black/50 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                    Địa điểm
                </span>
            </div>

            <!-- Content Overlay -->
            <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                <h3 class="mb-3 line-clamp-2 text-xl font-bold leading-tight">
                    {{ blog.title }}
                </h3>

                <!-- Address/Location Info -->
                <div v-if="blog.address || blog.location?.name"
                    class="mb-4 flex items-start gap-2 text-xs text-white/90">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                        class="w-4 h-4 mt-0.5 flex-shrink-0">
                        <path fill-rule="evenodd"
                            d="M11.54 22.351l.07.04.028.016a.76.76 0 00.723 0l.028-.015.071-.041a16.975 16.975 0 001.144-.742 19.58 19.58 0 002.683-2.282c1.944-1.99 3.963-4.98 3.963-8.827a8.25 8.25 0 00-16.5 0c0 3.846 2.02 6.837 3.963 8.827a19.58 19.58 0 002.682 2.282 16.975 16.975 0 001.145.742zM12 13.5a3 3 0 100-6 3 3 0 000 6z"
                            clip-rule="evenodd" />
                    </svg>
                    <span class="line-clamp-2">{{ blog.address || blog.location?.name }}</span>
                </div>

                <div class="flex items-center justify-between gap-4">
                    <!-- Category Badge -->
                    <div v-if="blog.category?.name"
                        class="rounded-xl bg-primary-500 px-3 py-2 text-xs font-bold uppercase tracking-wide text-white">
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
    detailRouteName: 'blog.show'
});

const cardMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;
</script>
