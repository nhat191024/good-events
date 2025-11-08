<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-6 lg:grid-cols-3">
                <motion.article
                    v-for="(channel, index) in channels"
                    :key="channel.title"
                    class="rounded-3xl border border-slate-100 bg-slate-50/80 p-6 shadow-sm shadow-black/[0.03]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="getCardTransition(index)"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">{{ channel.title }}</p>
                    <p class="mt-3 text-base text-slate-600">
                        {{ channel.description }}
                    </p>
                    <p class="mt-4 text-lg font-semibold text-slate-900">{{ channel.detail }}</p>
                    <p class="mt-2 text-sm text-slate-500">{{ channel.hint }}</p>
                </motion.article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface ContactChannel {
    title: string;
    description: string;
    detail: string;
    hint: string;
}

defineProps<{
    channels: ContactChannel[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 30 },
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
    delay: Math.min(index * 0.08, 0.35),
});
</script>
