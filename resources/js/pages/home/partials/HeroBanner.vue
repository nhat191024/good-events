<template>
    <section class="relative w-full">
        <div :class="cn('absolute inset-0 z-0', bgColorClass)"></div>
        <motion.div class="w-full" :initial="heroMotion.initial" :animate="heroMotion.animate">
            <div class="relative z-10 container mx-auto px-4 md:py-8 py-1">
                <div class="text-center md:mb-8 mb-1">
                    <h1 class="text-[25px] font-lexend font-medium md:text-[36px] text-white text-shadow-sm">
                        {{ headerText }}
                    </h1>
                </div>

                <div class="mb-8 flex justify-center">
                    <div class="w-full max-w-5xl h-[420px] bg-white rounded-4xl shadow-lg overflow-hidden">
                        <Swiper v-if="hasSlides" :modules="swiperModules"
                            :autoplay="{ delay: 4800, disableOnInteraction: false }" loop :space-between="24"
                            :slides-per-view="1" class="h-full">
                            <SwiperSlide v-for="slide in slides" :key="slide.key">
                                <div class="relative w-full h-full">
                                    <div class="relative w-full h-full hero-slide" v-html="slide.src"></div>
                                    <div
                                        class="absolute inset-x-0 bottom-0 px-6 pb-6 pt-14 bg-gradient-to-t from-black/80 to-transparent text-white">

                                    </div>
                                </div>
                            </SwiperSlide>
                        </Swiper>

                        <div v-else class="w-full h-full">
                            <img :src="headerBannerImg" alt="Hero banner" class="w-full h-full object-cover" />
                        </div>
                    </div>
                </div>


            </div>
        </motion.div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

import { motion } from 'motion-v';

import { Navigation, Pagination, Autoplay } from 'swiper/modules';

import 'swiper/css';
import 'swiper/css/navigation';
// import 'swiper/css/pagination';

type BannerImage = {
    image_tag?: string | null;
};

const props = withDefaults(defineProps<{
    modelValue?: string;
    headerText?: string;
    headerBannerImg?: string;
    bannerImages?: BannerImage[];
    bgColorClass?: string;
}>(), {
    headerText: 'Thuê đối tác xịn. Sự kiện thêm vui',
    headerBannerImg: '/images/banner-image.webp',
    bannerImages: [],
    bgColorClass:
        'bg-[linear-gradient(180deg,var(--token-3d956cea-dbc1-4944-ad45-eefce4d545dc,#f28f8f)_0%,var(--token-3d956cea-dbc1-4944-ad45-eefce4d545dc,rgb(242,143,143))_51.50151983037725%,rgba(242,143,143,0)_100%)]',
});

const emit = defineEmits<{ (e: 'update:modelValue', value: string): void }>();

const swiperModules = [Navigation, Autoplay];

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
