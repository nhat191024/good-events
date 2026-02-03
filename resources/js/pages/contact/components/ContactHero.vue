<template>
    <section class="bg-gradient-to-b from-indigo-600 to-indigo-500 text-white">
        <div class="mx-auto w-full max-w-6xl px-4 py-16 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-10 lg:flex-row lg:items-center">
                <div class="flex-1 space-y-6">
                    <motion.p class="text-sm font-semibold uppercase tracking-widest text-indigo-100"
                        :initial="textMotion.initial" :while-in-view="textMotion.visible"
                        :viewport="textMotion.viewport" :transition="textMotion.transition">
                        Kết nối với Sukientot
                    </motion.p>
                    <motion.h1 class="text-3xl font-bold sm:text-4xl" :initial="textMotion.initial"
                        :while-in-view="textMotion.visible" :viewport="textMotion.viewport"
                        :transition="getHeadingTransition()">
                        Chúng tôi luôn sẵn sàng lắng nghe
                    </motion.h1>
                    <motion.p class="text-lg text-indigo-50" :initial="textMotion.initial"
                        :while-in-view="textMotion.visible" :viewport="textMotion.viewport"
                        :transition="getParagraphTransition()">
                        Hãy cho chúng tôi biết nhu cầu của bạn, đội ngũ sẽ tư vấn giải pháp hoặc ghép nối chuyên gia phù
                        hợp trong thời gian sớm nhất.
                    </motion.p>
                    <ul class="space-y-3 text-sm text-indigo-100">
                        <motion.li v-for="(item, index) in heroHighlights" :key="item.label"
                            class="flex items-center gap-2" :initial="listMotion.initial"
                            :while-in-view="listMotion.visible" :viewport="listMotion.viewport"
                            :transition="getListTransition(index)">
                            <span class="h-2 w-2 rounded-full bg-emerald-300" />
                            <span>{{ item.label }} <strong>{{ item.value }}</strong></span>
                        </motion.li>
                    </ul>
                </div>
                <motion.div class="flex-1 rounded-3xl bg-white/10 p-6 shadow-2xl shadow-indigo-900/30 backdrop-blur"
                    :initial="cardMotion.initial" :while-in-view="cardMotion.visible" :viewport="cardMotion.viewport"
                    :transition="cardMotion.transition" :while-hover="cardInteractions.hover"
                    :while-tap="cardInteractions.tap">
                    <div class="space-y-4">
                        <div v-for="social in socialLinks" :key="social.label" class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" :src="social.icon" :alt="`${social.label} Icon`" loading="lazy">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary" :href="social.href"
                                target="_blank" rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    {{ social.label }}</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    {{ social.subtitle }}
                                </p>
                            </a>
                        </div>
                    </div>
                </motion.div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { computed } from 'vue';

const props = defineProps<{
    hotline?: string | null;
    socials?: {
        zalo?: string | null;
        facebook?: string | null;
        facebook_group?: string | null;
        youtube?: string | null;
        tiktok?: string | null;
    };
}>();

const socialLinks = computed(() =>
    [
        { label: 'Nhóm Zalo Đối Tác', href: props.socials?.zalo, icon: '/images/social/zalo.png', subtitle: 'Tham gia nhóm Zalo đối tác' },
        { label: 'Facebook', href: props.socials?.facebook, icon: '/images/social/facebook.png', subtitle: 'Theo dõi chúng tôi trên Facebook' },
        { label: 'Nhóm Cộng Đồng', href: props.socials?.facebook_group, icon: '/images/social/facebook.png', subtitle: 'Nhân sự - Sự kiện' },
        { label: 'YouTube', href: props.socials?.youtube, icon: '/images/social/youtube.png', subtitle: 'Xem kênh YouTube của chúng tôi' },
        { label: 'TikTok', href: props.socials?.tiktok, icon: '/images/social/tiktok.png', subtitle: 'Theo dõi TikTok của chúng tôi' },
    ].filter((item) => !!item.href)
);

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
