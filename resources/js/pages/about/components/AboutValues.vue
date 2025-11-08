<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-14 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Giá trị cốt lõi</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900">Những điều định hình Sukientot.com</h2>
            </div>
            <div class="mt-8 grid gap-6 sm:grid-cols-2">
                <motion.article
                    v-for="(value, index) in values"
                    :key="value.title"
                    class="rounded-3xl border border-slate-100 bg-slate-50/80 p-6 shadow-sm shadow-black/[0.03]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="getCardTransition(index)"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <h3 class="text-xl font-semibold text-slate-900">{{ value.title }}</h3>
                    <p class="mt-2 text-sm text-slate-600">
                        {{ value.description }}
                    </p>
                </motion.article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface CoreValue {
    title: string;
    description: string;
}

defineProps<{
    values: CoreValue[];
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
    delay: Math.min(index * 0.07, 0.35),
});
</script>
