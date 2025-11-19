<template>
    <motion.div
        class="h-full"
        :initial="cardMotion.initial"
        :while-in-view="cardMotion.visible"
        :viewport="cardMotion.viewport"
        :transition="cardMotion.transition"
    >
        <component
            :is="href ? 'a' : 'div'"
            :href="href ?? undefined"
            target="_blank"
            rel="noopener"
            class="group block h-full overflow-hidden rounded-3xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-lg"
        >
            <div class="relative aspect-video w-full overflow-hidden bg-gray-100">
                <img
                    v-if="blog.thumbnail"
                    :src="getImg(blog.thumbnail)"
                    :alt="blog.title"
                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                />
                <div v-else class="flex h-full w-full items-center justify-center bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 text-sm font-semibold text-white">
                    Video
                </div>
            </div>
            <div class="flex flex-col gap-2 p-5">
                <p v-if="blog.category?.name" class="text-xs font-semibold uppercase tracking-wide text-primary-500">
                    {{ blog.category.name }}
                </p>
                <h3 class="line-clamp-2 text-lg font-semibold text-gray-900">
                    {{ blog.title }}
                </h3>
                <p class="text-sm text-gray-500 line-clamp-2">
                    {{ blog.video_url ? blog.video_url : 'Chưa có đường dẫn video' }}
                </p>
                <div class="mt-auto flex items-center justify-between text-xs text-gray-400">
                    <span v-if="blog.published_human">{{ blog.published_human }}</span>
                    <span v-if="href" class="inline-flex items-center gap-1 text-primary-600">
                        Xem video
                        <span aria-hidden="true">↗</span>
                    </span>
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
