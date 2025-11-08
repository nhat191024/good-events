<template>
    <motion.section
        class="px-4 py-6"
        :initial="sectionMotion.initial"
        :while-in-view="sectionMotion.visible"
        :viewport="sectionMotion.viewport"
        :transition="sectionMotion.transition"
    >
        <motion.div
            class="mx-auto flex w-full max-w-6xl flex-col gap-6 rounded-3xl bg-gradient-to-r from-primary-400 via-primary-300 to-pink-300 p-6 text-white shadow-lg shadow-indigo-500/30 lg:flex-row lg:items-center lg:justify-between"
            :initial="cardMotion.initial"
            :while-in-view="cardMotion.visible"
            :viewport="cardMotion.viewport"
            :transition="cardMotion.transition"
        >
            <div class="space-y-2">
                <motion.p
                    class="text-sm font-semibold uppercase tracking-widest text-white/80"
                    :initial="textMotion.initial"
                    :while-in-view="textMotion.visible"
                    :viewport="textMotion.viewport"
                    :transition="textMotion.transition"
                >
                    Sukientot.com
                </motion.p>
                <motion.h2
                    class="text-2xl font-bold leading-snug lg:text-3xl"
                    :initial="textMotion.initial"
                    :while-in-view="textMotion.visible"
                    :viewport="textMotion.viewport"
                    :transition="getHeadingTransition()"
                >
                    Đặt show biểu diễn chỉ với một cú nhấp
                </motion.h2>
                <motion.p
                    class="text-sm text-white/90"
                    :initial="textMotion.initial"
                    :while-in-view="textMotion.visible"
                    :viewport="textMotion.viewport"
                    :transition="getParagraphTransition()"
                >
                    Tìm hiểu cách chúng tôi kết nối chú hề, MC, ảo thuật gia, workshop và mascot cho mọi sự kiện trong 30 giây.
                </motion.p>
            </div>
            <motion.div
                class="flex items-center gap-3"
                :initial="ctaMotion.initial"
                :while-in-view="ctaMotion.visible"
                :viewport="ctaMotion.viewport"
                :transition="ctaMotion.transition"
                :while-hover="ctaMotion.hover"
                :while-tap="ctaMotion.tap"
            >
                <Link
                    :href="route('about.index')"
                    class="inline-flex items-center justify-center rounded-2xl bg-white px-6 py-3 text-base font-semibold text-slate-900 shadow-sm shadow-black/10 transition hover:translate-y-0.5"
                >
                    Tìm hiểu thêm
                </Link>
            </motion.div>
        </motion.div>
    </motion.section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { Link } from '@inertiajs/vue3';

const sectionMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const cardMotion = {
    initial: { opacity: 0.4, scale: 0.96 },
    visible: { opacity: 1, scale: 1 },
    transition: { duration: 0.65, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const textMotion = {
    initial: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut' },
    viewport: { once: true, amount: 0.4 },
} as const;

const ctaMotion = {
    initial: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut', delay: 0.1 },
    viewport: { once: true, amount: 0.35 },
    hover: {
        scale: 1.03,
        transition: { type: 'spring', duration: 0.5, bounce: 0.28 },
    },
    tap: {
        scale: 0.96,
        transition: { type: 'spring', duration: 0.4, bounce: 0.22 },
    },
} as const;

const getHeadingTransition = () => ({
    ...textMotion.transition,
    delay: 0.08,
});

const getParagraphTransition = () => ({
    ...textMotion.transition,
    delay: 0.12,
});
</script>
