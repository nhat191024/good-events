<template>
    <section class="bg-gradient-to-b from-indigo-600 to-indigo-500 text-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-center">
                <div class="flex-1 space-y-6">
                    <motion.p
                        class="text-sm font-semibold uppercase tracking-widest text-indigo-100"
                        :initial="textMotion.initial"
                        :while-in-view="textMotion.visible"
                        :viewport="textMotion.viewport"
                        :transition="textMotion.transition"
                    >
                        Kết nối với Sukientot
                    </motion.p>
                    <motion.h1
                        class="text-3xl font-bold sm:text-4xl"
                        :initial="textMotion.initial"
                        :while-in-view="textMotion.visible"
                        :viewport="textMotion.viewport"
                        :transition="getHeadingTransition()"
                    >
                        Chúng tôi luôn sẵn sàng lắng nghe
                    </motion.h1>
                    <motion.p
                        class="text-lg text-indigo-50"
                        :initial="textMotion.initial"
                        :while-in-view="textMotion.visible"
                        :viewport="textMotion.viewport"
                        :transition="getParagraphTransition()"
                    >
                        Hãy cho chúng tôi biết nhu cầu của bạn, đội ngũ sẽ tư vấn giải pháp hoặc ghép nối chuyên gia phù hợp trong thời gian sớm nhất.
                    </motion.p>
                    <ul class="space-y-3 text-sm text-indigo-100">
                        <motion.li
                            v-for="(item, index) in heroHighlights"
                            :key="item.label"
                            class="flex items-center gap-2"
                            :initial="listMotion.initial"
                            :while-in-view="listMotion.visible"
                            :viewport="listMotion.viewport"
                            :transition="getListTransition(index)"
                        >
                            <span class="h-2 w-2 rounded-full bg-emerald-300" />
                            <span>{{ item.label }} <strong>{{ item.value }}</strong></span>
                        </motion.li>
                    </ul>
                </div>
                <motion.div
                    class="flex-1 rounded-3xl bg-white/10 p-6 shadow-2xl shadow-indigo-900/30 backdrop-blur"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <p class="text-sm font-medium uppercase tracking-wide text-indigo-200">Cam kết phản hồi</p>
                    <p class="mt-4 text-2xl font-semibold text-white">Trong vòng 2 giờ làm việc</p>
                    <p class="mt-3 text-base text-indigo-100">
                        Mọi thông tin của bạn được bảo mật và chỉ sử dụng để hỗ trợ yêu cầu đã gửi.
                    </p>
                </motion.div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';

const props = defineProps<{ hotline?: string | null }>();

const heroHighlights = computed(() => [
    { label: 'Hotline ưu tiên:', value: props.hotline ?? '1900 636 902' },
    { label: 'Giờ làm việc:', value: '7:00 - 22:00 (T2 - CN)' },
]);

const textMotion = {
    initial: { opacity: 0, y: 20 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.35 },
} as const;

const listMotion = {
    initial: { opacity: 0, x: -16 },
    visible: { opacity: 1, x: 0 },
    transition: { duration: 0.45, ease: 'easeOut' },
    viewport: { once: true, amount: 0.4 },
} as const;

const cardMotion = {
    initial: { opacity: 0, scale: 0.95 },
    visible: { opacity: 1, scale: 1 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.35 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.02,
        y: -6,
        transition: { type: 'spring', duration: 0.55, bounce: 0.3 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
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

const getListTransition = (index: number) => ({
    ...listMotion.transition,
    delay: Math.min(index * 0.05, 0.2),
});
</script>
