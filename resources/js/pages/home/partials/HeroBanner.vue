<template>
    <section class="relative w-full">
        <!-- Gradient Background -->
        <div :class="cn('absolute inset-0 z-0', bgColorClass)">
        </div>
        <motion.div class="w-full" :initial="heroMotion.initial" :animate="heroMotion.animate">
            <div class="relative z-10 container mx-auto px-4 md:py-8 py-1">
                <div class="text-center md:mb-8 mb-1">
                    <h1 class="text-[25px] font-lexend font-medium md:text-[36px] text-black mb-4">
                        {{ headerText }}
                    </h1>
                </div>

                <!-- Hero Image/Video -->
                <div class="mb-8 flex justify-center">
                    <div class="w-full max-w-4xl h-64 bg-white rounded-4xl shadow-lg overflow-hidden">
                        <img :src="headerBannerImg" alt="Hero Banner" class="w-full h-full object-cover" />
                    </div>
                </div>

                <!-- Search Bar (reused) -->
                <div class="max-w-5xl mx-auto">
                    <SearchBar :show-search-btn="false" :model-value="props.modelValue ?? ''"
                        @update:model-value="(v: string) => emit('update:modelValue', v)" />
                </div>
            </div>
        </motion.div>
    </section>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils';
import SearchBar from '@/pages/categories/partials/SearchBar.vue';
import { motion } from 'motion-v';

const props = withDefaults(defineProps<{
    modelValue?: string
    headerText?: string
    headerBannerImg?: string
    bgColorClass?: string
}>(), {
    headerText: 'Thuê đối tác xịn. Sự kiện thêm vui',
    headerBannerImg: '/images/banner-image.webp',
    bgColorClass: 'bg-[linear-gradient(180deg,var(--token-3d956cea-dbc1-4944-ad45-eefce4d545dc,#f28f8f)_0%,var(--token-3d956cea-dbc1-4944-ad45-eefce4d545dc,rgb(242,143,143))_51.50151983037725%,rgba(242,143,143,0)_100%)]',
});

const emit = defineEmits<{ (e: 'update:modelValue', value: string): void }>();

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
} as const
</script>
