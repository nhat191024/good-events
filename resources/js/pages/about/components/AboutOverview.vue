<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <motion.article
                    v-for="(section, index) in sections"
                    :key="section.title"
                    class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6 shadow-sm shadow-black/[0.03]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="getCardTransition(index)"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">{{ section.eyebrow }}</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">{{ section.title }}</h2>
                    <p class="mt-3 text-base text-slate-600">
                        {{ section.description }}
                    </p>
                    <ul v-if="section.points?.length" class="mt-4 space-y-2 text-sm text-slate-600">
                        <li
                            v-for="point in section.points"
                            :key="point"
                            class="flex items-start gap-2">
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500" />
                            <span>{{ point }}</span>
                        </li>
                    </ul>
                </motion.article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface OverviewSection {
    eyebrow: string;
    title: string;
    description: string;
    points?: string[];
}

defineProps<{
    sections: OverviewSection[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 35 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.02,
        y: -6,
        transition: { type: 'spring', duration: 0.5, bounce: 0.35 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const getCardTransition = (index: number) => ({
    ...cardMotion.transition,
    delay: Math.min(index * 0.08, 0.35),
});
</script>
