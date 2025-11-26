<template>
    <section class="relative w-full bg-white">
        <motion.div class="relative h-[620px] md:h-[60vh] w-full overflow-visible" :initial="heroMotion.initial"
            :animate="heroMotion.animate">
            <Swiper v-if="hasSlides" :modules="swiperModules"
                effect="fade" :fade-effect="{ crossFade: true }" :allow-touch-move="false"
                :autoplay="{ delay: 5200, disableOnInteraction: false }" loop :space-between="0"
                :slides-per-view="1" class="hero-swiper h-full">
                <SwiperSlide v-for="slide in slides" :key="slide.key">
                    <div class="hero-slide">
                        <div class="hero-media" v-html="slide.src"></div>
                        <div class="hero-overlay"></div>
                    </div>
                </SwiperSlide>
            </Swiper>

            <div v-else class="hero-slide">
                <img :src="getImg(headerBannerImg)" alt="Hero banner" class="hero-img" />
                <div class="hero-overlay"></div>
            </div>

            <div class="pointer-events-none absolute inset-x-0 -bottom-1 h-3 bg-gradient-to-b from-transparent via-25%-white/10 via-50%-white/30 via-75%-white/60 via-90%-white to-white z-[3]"
                aria-hidden="true"></div>

            <div class="hero-content h-full w-full px-4">
                <div class="hero-content-inner text-white space-y-4 w-full pl-0 md:pl-12">
                    <slot />
                </div>
            </div>
        </motion.div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { motion } from 'motion-v';

import { Autoplay, EffectFade } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/effect-fade';
import { getImg } from '@/pages/booking/helper';

type BannerImage = {
    image_tag?: string | null;
};

const props = withDefaults(defineProps<{
    modelValue?: string;
    headerBannerImg?: string;
    bannerImages?: BannerImage[];
}>(), {
    headerBannerImg: '/images/banner-image.webp',
    bannerImages: [],
});

const swiperModules = [Autoplay, EffectFade];

const slides = computed(() => {
    const images = props.bannerImages ?? [];

    return images
        .map((image, index) => {
            const src = image.image_tag ?? '';

            return {
                ...image,
                key: index,
                src
            };
        })
        .filter((slide) => Boolean(slide.src));
});

const hasSlides = computed(() => slides.value.length > 0);

const heroMotion = {
    initial: {
        opacity: 0.5,
        y: 40,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            duration: 0.6,
            ease: 'easeOut',
        },
    },
} as const;
</script>

<style scoped>
.hero-swiper {
    width: 100%;
    height: 100%;
}

.hero-slide {
    position: relative;
    width: 100%;
    height: 100%;
    overflow: hidden;
    background: #0b0f17;
}

.hero-media {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
}

.hero-media :deep(img),
.hero-img {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    color: transparent;
    filter: brightness(0.78) contrast(1.05) saturate(1.05);
}

.hero-overlay {
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 32% 22%, rgba(0, 82, 156, 0.24), transparent 35%),
        radial-gradient(circle at 72% 18%, rgba(0, 125, 255, 0.22), transparent 30%),
        linear-gradient(135deg, rgba(0, 0, 0, 0.35), rgb(0 7 18 / 0%));
    z-index: 2;
    pointer-events: none;
}

.hero-content {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    z-index: 5;
    pointer-events: none;
}

.hero-content-inner {
    padding-bottom: 2rem;
    pointer-events: auto;
}
</style>
