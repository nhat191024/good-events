<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-5xl px-4 py-16 sm:px-6 lg:px-8">
            <motion.div
                class="rounded-3xl border border-slate-200 bg-slate-50/70 p-8 shadow-sm shadow-black/[0.03]"
                :initial="cardMotion.initial"
                :while-in-view="cardMotion.visible"
                :viewport="cardMotion.viewport"
                :transition="cardMotion.transition"
            >
                <div class="text-center">
                    <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Câu hỏi thường gặp</p>
                    <h2 class="mt-3 text-3xl font-bold text-slate-900">Bạn đang thắc mắc điều gì?</h2>
                </div>
                <div class="mt-8 space-y-4">
                    <motion.details
                        v-for="(faq, index) in faqs"
                        :key="faq.question"
                        class="group rounded-2xl border border-slate-200 bg-white p-4 transition hover:border-indigo-200"
                        :initial="faqMotion.initial"
                        :while-in-view="faqMotion.visible"
                        :viewport="faqMotion.viewport"
                        :transition="getFaqTransition(index)"
                        :while-hover="faqInteractions.hover"
                        :while-tap="faqInteractions.tap"
                    >
                        <summary class="cursor-pointer text-base font-semibold text-slate-900">
                            {{ faq.question }}
                        </summary>
                        <p class="mt-3 text-sm text-slate-600">
                            {{ faq.answer }}
                        </p>
                    </motion.details>
                </div>
            </motion.div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface FAQItem {
    question: string;
    answer: string;
}

defineProps<{
    faqs: FAQItem[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 30 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const faqMotion = {
    initial: { opacity: 0, y: 15 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.45, ease: 'easeOut' },
    viewport: { once: true, amount: 0.4 },
} as const;

const faqInteractions = {
    hover: {
        scale: 1.01,
        y: -4,
        transition: { type: 'spring', duration: 0.5, bounce: 0.3 },
    },
    tap: {
        scale: 0.99,
        transition: { type: 'spring', duration: 0.35, bounce: 0.25 },
    },
} as const;

const getFaqTransition = (index: number) => ({
    ...faqMotion.transition,
    delay: Math.min(index * 0.05, 0.25),
});
</script>
