<template>
    <section class="bg-slate-50">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="rounded-3xl border border-slate-200 bg-white/80 p-8 shadow-sm shadow-black/[0.04]">
                <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <motion.article
                        v-for="(stat, index) in stats"
                        :key="stat.label"
                        class="rounded-2xl border border-slate-100 bg-white p-6 text-center shadow-sm shadow-black/[0.03]"
                        :initial="cardMotion.initial"
                        :while-in-view="cardMotion.visible"
                        :viewport="cardMotion.viewport"
                        :transition="getCardTransition(index)"
                        :while-hover="cardInteractions.hover"
                        :while-tap="cardInteractions.tap"
                    >
                        <p class="text-sm font-medium uppercase tracking-wide text-slate-500">{{ stat.label }}</p>
                        <p class="mt-3 text-3xl font-semibold text-slate-900">{{ stat.value }}</p>
                        <p class="mt-1 text-sm text-slate-500">{{ stat.subtext }}</p>
                    </motion.article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface StatItem {
    label: string;
    value: string;
    subtext: string;
}

defineProps<{
    stats: StatItem[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 35 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.05,
        y: -6,
        transition: { type: 'spring', duration: 0.55, bounce: 0.35 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const getCardTransition = (index: number) => ({
    ...cardMotion.transition,
    delay: Math.min(index * 0.05, 0.25),
});
</script>
