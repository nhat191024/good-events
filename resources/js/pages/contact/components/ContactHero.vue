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
                        <!-- Zalo: Minh -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/zalo.png" alt="Zalo Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://zalo.me/0908556913" target="_blank" rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    Zalo: Minh</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    0908.556.913
                                </p>
                            </a>
                        </div>

                        <!-- Zalo: Phương -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/zalo.png" alt="Zalo Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://zalo.me/0975038534" target="_blank" rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    Zalo: Phương</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    0975.038.534
                                </p>
                            </a>
                        </div>

                        <!-- Zalo Group Link -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/zalo.png" alt="Zalo Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://zalo.me/g/mudhks623" target="_blank" rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    Nhóm Zalo Đối Tác</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    Tham gia nhóm Zalo đối tác
                                </p>
                            </a>
                        </div>

                        <!-- Facebook Link -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/facebook.png" alt="Facebook Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://www.facebook.com/share/16WPcoU7x7/?mibextid=wwXIfr" target="_blank"
                                rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    Facebook</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    Theo dõi chúng tôi trên Facebook
                                </p>
                            </a>
                        </div>

                        <!-- Facebook Group Link -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/facebook.png" alt="Facebook Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://www.facebook.com/share/g/1D9PCXgbbq/?mibextid=wwXIfr" target="_blank"
                                rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    Nhóm Cộng Đồng</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    Nhân sự - Sự kiện
                                </p>
                            </a>
                        </div>

                        <!-- YouTube Link -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/youtube.png" alt="YouTube Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://youtube.com/@sukientot?si=00L9WnYoZyxfuPDb" target="_blank"
                                rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    YouTube</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    Xem kênh YouTube của chúng tôi
                                </p>
                            </a>
                        </div>

                        <!-- TikTok Link -->
                        <div class="flex items-start gap-4">
                            <div class="bg-primary/10 rounded-full p-3">
                                <img class="h-6 w-6 text-primary" src="/images/social/tiktok.png" alt="TikTok Icon">
                            </div>
                            <a class="group transition-colors duration-200 hover:text-primary"
                                href="https://www.tiktok.com/@sukien.com?_r=1&_t=ZS-92A2PZ54gxJ" target="_blank"
                                rel="noopener noreferrer">
                                <h3 class="font-semibold transition-all duration-200 group-hover:underline text-white">
                                    TikTok</h3>
                                <p
                                    class="text-indigo-100 transition-all duration-200 group-hover:text-primary group-hover:underline">
                                    Theo dõi TikTok của chúng tôi
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
