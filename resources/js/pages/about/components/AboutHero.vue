<template>
    <section class="bg-gradient-to-b from-sky-50 via-white to-white">
        <div
            class="mx-auto grid w-full max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-[1.05fr,1fr] lg:px-8">
            <div class="space-y-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-sky-600">{{ content.eyebrow }}</p>
                <h1 class="text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">
                    {{ content.title }}
                </h1>
                <p class="text-lg text-slate-600">
                    {{ content.description }}
                </p>
                <div class="flex flex-wrap gap-3">
                    <motion.div
                        v-for="(cta, index) in ctas"
                        :key="cta.href + cta.label"
                        :while-hover="buttonInteractions.hover"
                        :while-tap="buttonInteractions.tap"
                        :initial="buttonMotion.initial"
                        :while-in-view="buttonMotion.visible"
                        :viewport="buttonMotion.viewport"
                        :transition="getButtonTransition(index)"
                    >
                        <Link
                            :href="cta.href"
                            :class="cta.class"
                        >
                            {{ cta.label }}
                        </Link>
                    </motion.div>
                </div>
                <motion.div
                    class="rounded-3xl border border-slate-100 bg-white/80 p-4 shadow-sm shadow-black/[0.03]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition"
                >
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ content.subheading }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <motion.span
                            v-for="(service, index) in servicePills"
                            :key="service.label"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-1 text-sm font-medium text-slate-700"
                            :initial="pillMotion.initial"
                            :while-in-view="pillMotion.visible"
                            :viewport="pillMotion.viewport"
                            :transition="getPillTransition(index)"
                            :while-hover="pillInteractions.hover"
                            :while-tap="pillInteractions.tap"
                        >
                            {{ service.label }}
                        </motion.span>
                    </div>
                </motion.div>
                <div class="grid gap-3 sm:grid-cols-2">
                    <motion.article
                        v-for="(highlight, index) in highlights"
                        :key="highlight.title"
                        class="rounded-2xl border border-slate-100 bg-white/90 p-5 shadow-sm shadow-black/[0.03]"
                        :initial="cardMotion.initial"
                        :while-in-view="cardMotion.visible"
                        :viewport="cardMotion.viewport"
                        :transition="getCardTransition(index)"
                        :while-hover="cardInteractions.hover"
                        :while-tap="cardInteractions.tap"
                    >
                        <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">
                            {{ highlight.kicker }}
                        </p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ highlight.title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ highlight.description }}
                        </p>
                    </motion.article>
                </div>
            </div>

            <div class="relative">
                <div class="absolute -inset-6 -z-10 rounded-[36px] bg-gradient-to-br from-sky-100 via-white to-amber-50 blur-2xl" />
                <div class="grid gap-4 sm:grid-cols-2">
                    <motion.figure
                        v-for="(shot, index) in gallery"
                        :key="shot.label"
                        class="relative overflow-hidden rounded-3xl border border-slate-100/60 bg-white/60 shadow-lg shadow-black/[0.04]"
                        :style="{
                            backgroundImage: `linear-gradient(140deg, rgba(15,23,42,0.45), rgba(15,23,42,0.05)), url(${shot.image})`,
                            backgroundSize: 'cover',
                            backgroundPosition: 'center',
                        }"
                        :initial="shotMotion.initial"
                        :while-in-view="shotMotion.visible"
                        :viewport="shotMotion.viewport"
                        :transition="getShotTransition(index)"
                        :while-hover="shotInteractions.hover"
                        :while-tap="shotInteractions.tap"
                    >
                        <div class="flex h-full flex-col justify-between p-5 text-white">
                            <div class="inline-flex w-fit items-center rounded-full bg-white/20 px-3 py-1 text-xs font-semibold uppercase tracking-wide backdrop-blur">
                                {{ shot.label }}
                            </div>
                            <p class="mt-10 text-sm font-medium leading-relaxed text-white/90">
                                {{ shot.caption }}
                            </p>
                        </div>
                    </motion.figure>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { Link } from '@inertiajs/vue3';

interface Highlight {
    kicker: string;
    title: string;
    description: string;
}

interface HeroContent {
    eyebrow: string;
    title: string;
    description: string;
    subheading: string;
}

interface ServicePill {
    label: string;
}

interface HeroShot {
    label: string;
    caption: string;
    image: string;
}

defineProps<{
    content: HeroContent;
    highlights: Highlight[];
    servicePills: ServicePill[];
    gallery: HeroShot[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 30 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const cardInteractions = {
    hover: {
        y: -6,
        scale: 1.02,
        transition: { type: 'spring', duration: 0.55, bounce: 0.35 },
    },
    tap: {
        scale: 0.98,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const pillMotion = {
    initial: { opacity: 0, y: 10 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.2 },
} as const;

const pillInteractions = {
    hover: {
        scale: 1.05,
        transition: { type: 'spring', duration: 0.45, bounce: 0.4 },
    },
    tap: {
        scale: 0.95,
        transition: { type: 'spring', duration: 0.35, bounce: 0.25 },
    },
} as const;

const buttonInteractions = {
    hover: {
        scale: 1.03,
        y: -4,
        transition: { type: 'spring', duration: 0.5, bounce: 0.3 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.2 },
    },
} as const;

const buttonMotion = {
    initial: { opacity: 0, y: 25 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.25 },
} as const;

const shotMotion = {
    initial: { opacity: 0, y: 30 },
    visible: { opacity: 1, y: 0 },
    viewport: { once: true, amount: 0.3 },
} as const;

const shotInteractions = {
    hover: {
        scale: 1.02,
        y: -4,
        transition: { type: 'spring', duration: 0.5, bounce: 0.35 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const ctas = [
    {
        href: route('register'),
        label: 'Đăng ký ngay',
        class: 'inline-flex items-center justify-center rounded-2xl bg-sky-600 px-6 py-3 text-base font-semibold text-white shadow-sm shadow-sky-600/30 transition hover:bg-sky-500',
    },
    {
        href: route('tutorial.index'),
        label: 'Xem demo',
        class: 'inline-flex items-center justify-center rounded-2xl border border-slate-200 px-6 py-3 text-base font-semibold text-slate-700 transition hover:border-slate-300',
    },
] as const;

const getCardTransition = (index: number) => ({
    ...cardMotion.transition,
    delay: Math.min(index * 0.06, 0.4),
});

const getPillTransition = (index: number) => ({
    duration: 0.45,
    delay: Math.min(index * 0.03, 0.25),
    ease: 'easeOut',
});

const getButtonTransition = (index: number) => ({
    duration: 0.5,
    delay: 0.1 + index * 0.05,
    ease: 'easeOut',
});

const getShotTransition = (index: number) => ({
    ...shotMotion.transition,
    delay: Math.min(index * 0.08, 0.32),
});
</script>
