<template>
    <motion.div :initial="iconsMotion.initial" :animate="iconsMotion.animate">
        <Link :href="route('partner-categories.show', partnerCategory.slug)" class="block">
            <div
                class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
                <!-- Heart icon -->
                <div class="relative">
                    <div class="absolute hidden top-3 right-3 z-10">
                        <button class="p-1 rounded-full bg-white/80 hover:bg-white transition-colors">
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                            </svg>
                        </button>
                    </div>
                    <div class="bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center">
                        <ImageWithLoader :src="getImg(partnerCategory.image)" :alt="partnerCategory.name"
                            class="w-full h-full aspect-square" img-class="w-full h-full object-cover" loading="lazy" />
                    </div>
                </div>
                <!-- Content -->
                <div class="p-4">
                    <h3 class="font-semibold text-gray-900 text-md mb-1">
                        {{ partnerCategory.name }}
                    </h3>
                </div>
            </div>
        </Link>
    </motion.div>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PartnerCategory } from '@/types/database';
import { motion } from 'motion-v';
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { inject } from "vue";

const route = inject('route') as any;

interface Props {
    partnerCategory: PartnerCategory & { image?: string | null };
}

const props = defineProps<Props>();

const formatPrice = (price: number): string => {
    return new Intl.NumberFormat('vi-VN', {
        style: 'currency',
        currency: 'VND',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(price).replace('₫', 'đ');
};

const iconsMotion = {
    initial: {
        opacity: 0.5,
        y: 20,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            delay: 0.35,
            duration: 0.6,
            ease: 'easeOut',
        },
    },
} as const

</script>
