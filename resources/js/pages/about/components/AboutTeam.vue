<template>
    <section class="bg-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="text-center">
                <p class="text-sm font-semibold uppercase tracking-wide text-indigo-600">Đội ngũ</p>
                <h2 class="mt-3 text-3xl font-bold text-slate-900">Những thành viên tại Sukientot</h2>
                <p class="mx-auto mt-4 max-w-2xl text-base text-slate-600">
                    Kết hợp chuyên môn về sự kiện, công nghệ và vận hành để mang tới trải nghiệm tốt cho khách hàng.
                </p>
            </div>

            <div class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <motion.article
                    v-for="(member, index) in members"
                    :key="member.name"
                    class="rounded-2xl border border-slate-100 bg-slate-50/70 p-6 text-center shadow-sm shadow-black/[0.02]"
                    :initial="cardMotion.initial"
                    :while-in-view="cardMotion.visible"
                    :viewport="cardMotion.viewport"
                    :transition="getCardTransition(index)"
                    :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap"
                >
                    <motion.div
                        class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-indigo-100 text-xl font-semibold text-indigo-700"
                        :initial="avatarMotion.initial"
                        :while-in-view="avatarMotion.visible"
                        :viewport="avatarMotion.viewport"
                        :transition="avatarMotion.transition"
                        :while-hover="avatarMotion.hover"
                        :while-tap="avatarMotion.tap"
                    >
                        {{ getInitials(member.name) }}
                    </motion.div>
                    <h3 class="mt-4 text-lg font-semibold text-slate-900">{{ member.name }}</h3>
                    <p class="text-sm font-medium text-indigo-600">{{ member.role }}</p>
                    <p class="mt-3 text-sm text-slate-600">
                        {{ member.description }}
                    </p>
                </motion.article>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
interface TeamMember {
    name: string;
    role: string;
    description: string;
}

defineProps<{
    members: TeamMember[];
}>();

function getInitials(name: string): string {
    return name
        .split(' ')
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0]?.toUpperCase() ?? '')
        .join('');
}

const cardMotion = {
    initial: { opacity: 0, y: 40 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.55, ease: 'easeOut' },
    viewport: { once: true, amount: 0.3 },
} as const;

const cardInteractions = {
    hover: {
        scale: 1.03,
        y: -6,
        transition: { type: 'spring', duration: 0.55, bounce: 0.35 },
    },
    tap: {
        scale: 0.97,
        transition: { type: 'spring', duration: 0.4, bounce: 0.25 },
    },
} as const;

const avatarMotion = {
    initial: { opacity: 0, scale: 0.9 },
    visible: { opacity: 1, scale: 1 },
    transition: { duration: 0.45, ease: 'easeOut' },
    viewport: { once: true, amount: 0.4 },
    hover: {
        scale: 1.08,
        transition: { type: 'spring', duration: 0.5, bounce: 0.4 },
    },
    tap: {
        scale: 0.95,
        transition: { type: 'spring', duration: 0.35, bounce: 0.3 },
    },
} as const;

const getCardTransition = (index: number) => ({
    ...cardMotion.transition,
    delay: Math.min(index * 0.08, 0.4),
});
</script>
