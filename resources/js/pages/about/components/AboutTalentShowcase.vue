<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center">
                <div class="lg:w-1/3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Danh mục nhân sự nổi bật</p>
                    <h2 class="mt-3 text-3xl font-bold text-slate-900">Đầy đủ nhân sự & tiết mục cho mọi sự kiện</h2>
                    <p class="mt-4 text-base text-slate-600">
                        Chỉ cần chọn chủ đề, Sukientot gợi ý đúng nghệ sĩ, tiết mục và workshop phù hợp với ngân sách.
                    </p>
                </div>
                <div class="grid flex-1 gap-4 sm:grid-cols-2">
                    <motion.article
                        v-for="(category, index) in categories"
                        :key="category.title"
                        class="relative overflow-hidden rounded-3xl p-5 shadow-sm shadow-black/[0.03]"
                        :class="category.cover ? 'border-0 text-white' : 'border border-slate-100 bg-slate-50/80 text-slate-900'"
                        :style="category.cover
                            ? {
                                backgroundImage: `linear-gradient(150deg, rgba(15,23,42,0.75), rgba(15,23,42,0.35)), url(${category.cover})`,
                                backgroundSize: 'cover',
                                backgroundPosition: 'center',
                            }
                            : undefined"
                        :initial="cardMotion.initial"
                        :while-in-view="cardMotion.visible"
                        :viewport="cardMotion.viewport"
                        :transition="getCardTransition(index)"
                        :while-hover="cardInteractions.hover"
                        :while-tap="cardInteractions.tap"
                    >
                        <div class="relative space-y-2">
                            <h3 class="text-lg font-semibold" :class="category.cover ? 'text-white' : 'text-slate-900'">
                                {{ category.title }}
                            </h3>
                            <p class="text-sm" :class="category.cover ? 'text-white/80' : 'text-slate-600'">
                                {{ category.description }}
                            </p>
                        </div>
                        <div class="relative mt-3 flex flex-wrap gap-2">
                            <motion.span
                                v-for="(tag, tagIndex) in category.tags"
                                :key="tag"
                                class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                :class="category.cover ? 'bg-white/20 text-white' : 'bg-white text-slate-600'"
                                :initial="tagMotion.initial"
                                :while-in-view="tagMotion.visible"
                                :viewport="tagMotion.viewport"
                                :transition="getTagTransition(tagIndex)"
                                :while-hover="tagInteractions.hover"
                                :while-tap="tagInteractions.tap"
                            >
                                {{ tag }}
                            </motion.span>
                        </div>
                    </motion.article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface TalentCategory {
    title: string;
    description: string;
    tags: string[];
    cover?: string;
}

defineProps<{
    categories: TalentCategory[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 35 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.02,
        y: -5,
        transition: { type: 'spring', duration: 0.5, bounce: 0.35 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const tagMotion = {
    initial: { opacity: 0, y: 10 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.4, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const tagInteractions = {
    hover: {
        scale: 1.05,
        transition: { type: 'spring', duration: 0.45, bounce: 0.35 },
    },
    tap: {
        scale: 0.95,
        transition: { type: 'spring', duration: 0.35, bounce: 0.25 },
    },
} as const;

const getCardTransition = (index: number) => ({
    ...cardMotion.transition,
    delay: Math.min(index * 0.08, 0.35),
});

const getTagTransition = (index: number) => ({
    ...tagMotion.transition,
    delay: Math.min(index * 0.04, 0.24),
});
</script>
