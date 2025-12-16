<template>
    <section class="bg-slate-900 text-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <motion.article
                    class="rounded-3xl border border-white/10 bg-white/10 p-6 backdrop-blur"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-200">Tầm nhìn</p>
                    <h3 class="mt-3 text-2xl font-semibold">{{ vision }}</h3>
                    <p class="mt-4 text-base text-indigo-100">
                        Trở thành lựa chọn đầu tiên khi khách hàng tìm kiếm nhân sự biểu diễn hoặc nhà cung cấp sự kiện.
                    </p>
                </motion.article>
                <motion.article
                    class="rounded-3xl border border-white/10 bg-white/5 p-6 backdrop-blur"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-200">Sứ mệnh</p>
                    <ul class="mt-4 space-y-3 text-base text-indigo-100">
                        <motion.li
                            v-for="(mission, index) in missionPoints"
                            :key="mission"
                            class="flex items-start gap-3"
                            :initial="listMotion.initial"
                            :while-in-view="listMotion.visible"
                            :viewport="listMotion.viewport"
                            :transition="getListTransition(index)"
                        >
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-300" />
                            <span>{{ mission }}</span>
                        </motion.li>
                    </ul>
                </motion.article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';

defineProps<{
    vision: string;
    missionPoints: string[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 30 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.02,
        y: -6,
        transition: { type: 'spring', duration: 0.55, bounce: 0.35 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const listMotion = {
    initial: { opacity: 0, x: -12 },
    visible: { opacity: 1, x: 0 },
    transition: { duration: 0.45, ease: 'easeOut' },
    viewport: { once: true, amount: 0.4 },
} as const;

const getListTransition = (index: number) => ({
    ...listMotion.transition,
    delay: Math.min(index * 0.04, 0.24),
});
</script>
