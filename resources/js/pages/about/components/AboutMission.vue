<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="grid gap-8 lg:grid-cols-2">
                <motion.article
                    class="rounded-3xl border border-slate-100 bg-slate-50/70 p-6 shadow-sm shadow-black/[0.02]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Nguyên tắc vận hành</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Mọi thành viên đều minh bạch và cam kết chuẩn</h2>
                    <ul class="mt-4 space-y-4 text-sm text-slate-600">
                        <motion.li
                            v-for="(principle, index) in principles"
                            :key="principle"
                            class="flex items-start gap-3"
                            :initial="listMotion.initial"
                            :while-in-view="listMotion.visible"
                            :viewport="listMotion.viewport"
                            :transition="getListTransition(index)"
                        >
                            <span class="mt-1 h-2 w-2 rounded-full bg-sky-500" />
                            <span>{{ principle }}</span>
                        </motion.li>
                    </ul>
                </motion.article>

                <motion.article
                    class="rounded-3xl border border-slate-100 bg-white p-6 shadow-sm shadow-black/[0.02]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">Cam kết & trách nhiệm</p>
                    <h2 class="mt-3 text-2xl font-bold text-slate-900">Bảo vệ lợi ích của khách hàng và nhân sự</h2>
                    <ul class="mt-4 space-y-4 text-sm text-slate-600">
                        <motion.li
                            v-for="(commitment, index) in commitments"
                            :key="commitment"
                            class="flex items-start gap-3"
                            :initial="listMotion.initial"
                            :while-in-view="listMotion.visible"
                            :viewport="listMotion.viewport"
                            :transition="getListTransition(index)"
                        >
                            <span class="mt-1 h-2 w-2 rounded-full bg-emerald-500" />
                            <span>{{ commitment }}</span>
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
    principles: string[];
    commitments: string[];
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
        transition: { type: 'spring', duration: 0.5, bounce: 0.35 },
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
