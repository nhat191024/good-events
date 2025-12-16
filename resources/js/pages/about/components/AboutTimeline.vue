<template>
    <section class="bg-slate-900 text-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-start">
                <div class="lg:w-1/3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-300">Hành trình</p>
                    <h2 class="mt-3 text-3xl font-bold">Những cột mốc đáng nhớ</h2>
                    <p class="mt-4 text-base text-slate-200">
                        Chúng tôi liên tục thử nghiệm và cải thiện sản phẩm để đáp ứng nhu cầu ngày càng phức tạp của doanh nghiệp.
                    </p>
                </div>
                <div class="flex-1 space-y-6">
                    <motion.article
                        v-for="(milestone, index) in milestones"
                        :key="milestone.year"
                        class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
                        :initial="cardMotion.initial"
                        :while-in-view="cardMotion.visible"
                        :viewport="cardMotion.viewport"
                        :transition="getCardTransition(index)"
                        :while-hover="cardInteractions.hover"
                        :while-tap="cardInteractions.tap"
                    >
                        <p class="text-sm font-semibold uppercase tracking-widest text-indigo-200">{{ milestone.year }}</p>
                        <h3 class="mt-2 text-2xl font-semibold">{{ milestone.title }}</h3>
                        <p class="mt-3 text-base text-slate-200">
                            {{ milestone.description }}
                        </p>
                    </motion.article>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface Milestone {
    year: string;
    title: string;
    description: string;
}

defineProps<{
    milestones: Milestone[];
}>();

const cardMotion = {
    initial: { opacity: 0, x: 40 },
    visible: { opacity: 1, x: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
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
    delay: Math.min(index * 0.08, 0.4),
});
</script>
