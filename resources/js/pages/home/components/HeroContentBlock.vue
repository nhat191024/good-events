<template>
    <motion.div class="w-full max-w-4xl space-y-6" :initial="containerMotion.initial" :animate="containerMotion.animate">
        <motion.div v-if="tagLabel"
            class="inline-flex items-center rounded-full bg-white/10 px-4 py-2 text-xs font-semibold uppercase tracking-[0.2em] text-white/80 backdrop-blur"
            :initial="itemMotion.initial" :animate="itemMotion.animate" :transition="tagTransition">
            {{ tagLabel }}
        </motion.div>
        <motion.h1 class="text-3xl md:text-5xl font-lexend font-semibold leading-tight text-white drop-shadow"
            :initial="itemMotion.initial" :animate="itemMotion.animate" :transition="headlineTransition">
            {{ title }}
        </motion.h1>
        <motion.p v-if="description" class="text-base md:text-lg text-white/80 max-w-3xl"
            :initial="itemMotion.initial" :animate="itemMotion.animate" :transition="bodyTransition">
            {{ description }}
        </motion.p>
        <motion.div class="flex flex-wrap items-center gap-3" :initial="itemMotion.initial"
            :animate="itemMotion.animate" :transition="ctaTransition">
            <a v-if="primaryCta" :href="primaryCta.href"
                class="inline-flex items-center rounded-full bg-white px-5 py-3 text-sm font-semibold text-slate-900 shadow-lg shadow-black/20 transition hover:-translate-y-0.5 hover:shadow-xl hover:shadow-black/25">
                {{ primaryCta.label }}
            </a>
            <Link v-if="secondaryCta" :href="secondaryCta.href"
                class="inline-flex items-center rounded-full border border-white/40 px-5 py-3 text-sm font-semibold text-white/90 backdrop-blur transition hover:bg-white/10 hover:border-white/60">
                {{ secondaryCta.label }}
            </Link>
        </motion.div>
        <div v-if="stats && stats.length" class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-white/80">
            <motion.div v-for="(item, index) in stats" :key="item.label"
                class="rounded-2xl bg-white/5 p-4 backdrop-blur"
                :initial="itemMotion.initial" :animate="itemMotion.animate"
                :transition="getStatTransition(index)">
                <p class="text-3xl font-bold text-white">{{ item.value }}</p>
                <p class="text-sm">{{ item.label }}</p>
            </motion.div>
        </div>
    </motion.div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';

type CTA = {
    label: string;
    href: string;
};

type Stat = {
    value: string;
    label: string;
};

withDefaults(defineProps<{
    tagLabel?: string;
    title: string;
    description?: string;
    primaryCta?: CTA;
    secondaryCta?: CTA;
    stats?: Stat[];
}>(), {
    tagLabel: '',
    description: '',
    primaryCta: undefined,
    secondaryCta: undefined,
    stats: () => [],
});

const containerMotion = {
    initial: { opacity: 0, y: 24 },
    animate: {
        opacity: 1,
        y: 0,
        transition: { duration: 0.7, ease: 'easeOut', staggerChildren: 0.08 },
    },
} as const;

const itemMotion = {
    initial: { opacity: 0, y: 14 },
    animate: { opacity: 1, y: 0 },
} as const;

const tagTransition = { duration: 0.5, ease: 'easeOut', delay: 0.05 };
const headlineTransition = { duration: 0.55, ease: 'easeOut', delay: 0.1 };
const bodyTransition = { duration: 0.55, ease: 'easeOut', delay: 0.16 };
const ctaTransition = { duration: 0.55, ease: 'easeOut', delay: 0.22 };

const getStatTransition = (index: number) => ({
    duration: 0.5,
    ease: 'easeOut',
    delay: 0.3 + index * 0.06,
});
</script>
