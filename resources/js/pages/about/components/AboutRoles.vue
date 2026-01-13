<template>
    <section class="bg-slate-900 text-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div class="space-y-3">
                    <p class="text-sm font-semibold uppercase tracking-wide text-sky-200">Hai vai trò chính</p>
                    <h2 class="text-3xl font-bold sm:text-4xl">Khách hàng & Đối tác</h2>
                    <p class="max-w-2xl text-base text-slate-200/80">
                        Lướt nhanh qua hai hành trình trọng tâm của Sukientot.com: khách hàng đặt lịch tức thì và nhân
                        sự nhận show đều đặn.
                    </p>
                </div>
                <div class="flex flex-wrap gap-2 text-sm text-slate-200/80">
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-3 py-2">
                        <span class="h-2 w-2 rounded-full bg-emerald-300" />
                        Đăng ký miễn phí
                    </span>
                    <span
                        class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/5 px-3 py-2">
                        <span class="h-2 w-2 rounded-full bg-sky-300" />
                        Hỗ trợ 24/7
                    </span>
                </div>
            </div>

            <div class="mt-10 grid gap-4 lg:grid-cols-[1.05fr,0.95fr]">
                <motion.article v-for="(role, index) in roles" :key="role.slug"
                    class="group relative overflow-hidden rounded-3xl border border-white/10 bg-white/5"
                    :class="role.emphasis ? 'ring-2 ring-sky-300/60' : 'ring-1 ring-white/10'" :style="{
                        backgroundImage: `linear-gradient(140deg, rgba(15,23,42,0.65), rgba(15,23,42,0.25)), url(${role.image})`,
                        backgroundSize: 'cover',
                        backgroundPosition: 'center',
                    }" :initial="cardMotion.initial" :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport" :transition="getCardTransition(index)"
                    :while-hover="cardInteractions.hover" :while-tap="cardInteractions.tap">
                    <div
                        class="absolute inset-0 bg-gradient-to-t from-slate-950/70 via-slate-900/30 to-slate-900/10 transition duration-300 group-hover:from-slate-950/80 group-hover:via-slate-900/40 group-hover:to-slate-900/15" />
                    <div class="relative flex h-full flex-col justify-between p-7 sm:p-8">
                        <div class="space-y-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="inline-flex items-center rounded-full bg-white/15 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-sky-50">
                                    {{ role.badge }}
                                </span>
                                <span v-if="role.emphasis"
                                    class="inline-flex items-center rounded-full bg-amber-400/90 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-amber-900">
                                    Ưu tiên
                                </span>
                            </div>
                            <div class="space-y-2">
                                <h3 class="text-2xl font-bold sm:text-3xl">{{ role.title }}</h3>
                                <p class="text-base text-slate-100/80">{{ role.summary }}</p>
                            </div>
                            <ul class="space-y-2 text-sm text-slate-100/80">
                                <li v-for="point in role.points" :key="point" class="flex items-start gap-2">
                                    <span class="mt-1 h-2 w-2 rounded-full bg-sky-300" />
                                    <span>{{ point }}</span>
                                </li>
                            </ul>
                        </div>
                        <div class="mt-6 flex items-center justify-between">
                            <Link :href="route('partner.register')"
                                class="inline-flex items-center gap-2 rounded-2xl bg-white/90 px-5 py-3 text-sm font-semibold text-slate-900 transition hover:bg-white">
                                Chọn vai trò này
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5l7 7-7 7" />
                                </svg>
                            </Link>
                            <span
                                class="rounded-full bg-white/10 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-100/80">
                                {{ role.slug === 'client' ? 'Dành cho khách' : 'Dành cho đối tác' }}
                            </span>
                        </div>
                    </div>
                </motion.article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { Link } from '@inertiajs/vue3';
import { inject } from "vue";

const route = inject('route') as any;

interface RoleCard {
    slug: string;
    badge: string;
    title: string;
    summary: string;
    points: string[];
    image: string;
    emphasis?: boolean;
}

defineProps<{
    roles: RoleCard[];
}>();

const cardMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
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

const getCardTransition = (index: number) => ({
    ...cardMotion.transition,
    delay: Math.min(index * 0.08, 0.35),
});
</script>
