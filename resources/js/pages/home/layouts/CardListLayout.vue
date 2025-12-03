<template>
    <div class="container mx-auto px-4 py-4 md:py-6 space-y-12">
        <motion.section v-if="showSection" :initial="sectionMotion.initial" :animate="sectionMotion.animate"
            class="mb-4">
            <!-- title -->
            <Link :href="href" :class="cn((href ? 'cursor-pointer' : ''))">
                <h2
                    :class="cn('text-xl font-bold text-gray-900 mb-6', (href ? 'cursor-pointer hover:underline' : ''))">
                    {{ name }}
                </h2>
            </Link>

            <Swiper v-if="items.length" :modules="[Navigation, Autoplay]" :slides-per-view="1.25" :space-between="8"
                :navigation="true" :autoplay="autoplayOptions" :loop="items.length > 3"
                :breakpoints="swiperBreakpoints"
                class="group/card-swiper card-list-swiper home-swiper-nav !pb-2"
                style="--swiper-nav-right-mobile: 4px; --swiper-nav-right-desktop: 4px; --swiper-nav-top: 120px; --swiper-nav-translate: none;"
                @reachEnd="handleReachEnd">
                <SwiperSlide v-for="(item, index) in items" :key="getItemKey(item, index)" class="!h-auto">
                    <slot :item="item" :index="index" />
                </SwiperSlide>
            </Swiper>
        </motion.section>
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
// import { Swiper, SwiperSlide } from 'swiper/vue';
// import { Navigation, Autoplay } from 'swiper/modules';
import { motion } from 'motion-v';
import 'swiper/css';
import 'swiper/css/navigation';
import '../styles/swiper-nav.css';
import { cn } from '@/lib/utils';

interface Props {
    name: string;
    showSection: true;
    href?: string;
    items: unknown[];
}

const emit = defineEmits<{
    (e: 'reach-end'): void;
}>();

const props = withDefaults(defineProps<Props>(), {
    items: () => [],
});

const items = computed(() => props.items ?? []);

const swiperBreakpoints = {
    640: { slidesPerView: 2, spaceBetween: 12 },
    768: { slidesPerView: 3, spaceBetween: 16 },
    1024: { slidesPerView: 4, spaceBetween: 20 },
};

const autoplayOptions = {
    delay: 4500,
    disableOnInteraction: false,
    pauseOnMouseEnter: true,
};

const handleReachEnd = () => emit('reach-end');

const getItemKey = (item: any, index: number) => item?.id ?? item?.slug ?? item?.key ?? index;

const sectionMotion = {
    initial: {
        opacity: 0.5,
        y: 24,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            delay: 0.3,
            duration: 0.6,
            ease: 'easeOut',
        },
    },
} as const;

</script>
