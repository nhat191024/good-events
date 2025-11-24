<template>
    <motion.div class="h-full" :initial="cardMotion.initial" :while-in-view="cardMotion.visible"
        :viewport="cardMotion.viewport" :transition="cardMotion.transition">
        <component :is="href ? 'a' : 'div'" :href="href ?? undefined" target="_blank" rel="noopener"
            class="group relative flex h-full flex-col overflow-hidden rounded-[2rem] bg-white shadow-sm transition-all duration-300 hover:shadow-xl"
            :class="{ 'cursor-pointer': href }">
            <!-- Thumbnail Section -->
            <div class="relative aspect-[4/5] w-full overflow-hidden">
                <img v-if="blog.thumbnail" :src="getImg(blog.thumbnail)" :alt="blog.title"
                    class="h-full w-full object-cover transition duration-700 group-hover:scale-110" />
                <div v-else class="flex h-full w-full items-center justify-center bg-slate-800 text-white">
                    <span class="text-lg font-medium">No Thumbnail</span>
                </div>

                <!-- Overlay Gradient -->
                <div
                    class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-60 transition-opacity duration-300 group-hover:opacity-70">
                </div>

                <!-- Play Button Overlay -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <div
                        class="flex h-16 w-16 items-center justify-center rounded-2xl bg-white/20 backdrop-blur-sm transition-transform duration-300 group-hover:scale-110">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor"
                            class="h-8 w-8 text-white">
                            <path fill-rule="evenodd"
                                d="M4.5 5.653c0-1.426 1.529-2.33 2.779-1.643l11.54 6.348c1.295.712 1.295 2.573 0 3.285L7.28 19.991c-1.25.687-2.779-.217-2.779-1.643V5.653z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </div>

                <div class="absolute right-4 top-4">
                    <span
                        class="rounded-lg bg-black/50 px-2 py-1 text-[10px] font-bold uppercase tracking-wider text-white backdrop-blur-md">
                        Video
                    </span>
                </div>

                <!-- Content Overlay -->
                <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                    <h3 class="mb-3 line-clamp-2 text-xl font-bold leading-tight">
                        {{ blog.title }}
                    </h3>

                    <!-- Tags -->
                    <div v-if="blog.tags && blog.tags.length > 0" class="mb-4 flex flex-wrap gap-2">
                        <span v-for="tag in blog.tags" :key="tag.id"
                            class="rounded-md bg-white/20 px-2 py-1 text-[10px] font-medium uppercase tracking-wide text-white backdrop-blur-sm">
                            {{ tag.name }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between gap-4">
                        <!-- Category Badge -->
                        <div v-if="blog.category?.name"
                            class="rounded-xl bg-red-500 px-3 py-2 text-xs font-bold uppercase tracking-wide text-white">
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
        </component>
    </motion.div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { motion } from 'motion-v';
import type { BlogSummary } from '../../types';
import { getImg } from '@/pages/booking/helper';

const props = defineProps<{ blog: BlogSummary }>();

const href = computed(() => props.blog.video_url ?? null);

const cardMotion = {
    initial: { opacity: 0.5, y: 24 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;
</script>
