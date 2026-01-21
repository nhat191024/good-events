<template>
    <Link :href="routeHref" class="block h-full">
        <motion.div :initial="cardMotion.initial" :animate="cardMotion.animate"
            class="card bg-base-100 shadow-sm group relative overflow-hidden cursor-pointer rounded-md">
            <figure class="relative aspect-[4/3]">
                <ImageWithLoader
                    :img-tag="cardItem.image_tag || undefined"
                    :src="cardImageSrc ?? undefined"
                    :alt="cardItem.name"
                    loading="lazy"
                    class="w-full h-full"
                    img-class="w-full h-full object-cover lazy-image"
                />
                <div v-if="showInfo"
                    class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                </div>
            </figure>
            <div v-if="showInfo"
                class="card-body absolute inset-0 shadow-2xl rounded-2xl opacity-100 duration-300 text-white flex flex-col justify-center p-4 overflow-scroll scrollbar-hide">
                <div class="overflow-y-scroll scrollbar-hide">
                    <p class="text-[0.75rem] sm:text-[0.80rem] md:text-[1rem] scrollbar-hide">
                        <h4 class="font-semibold block">{{ truncatedName }}</h4>
                    </p>
                </div>
                <div class="card-actions justify-end mt-auto pt-2"></div>
            </div>
        </motion.div>
    </Link>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Props } from './types';
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { motion } from 'motion-v';

const props = withDefaults(defineProps<Props>(), {
    showInfo: true
});

const currentImage = ref(getImg(props.cardItem.image));

watch(
    () => props.cardItem.image,
    (newImage) => {
        currentImage.value = getImg(newImage);
    }
);

const cardImageSrc = computed(() => {
    if (props.cardItem.image_tag) return null;
    return currentImage.value;
});

const truncate = (value: string | null | undefined, limit: number) => {
    if (!value) return '';
    return value.length > limit ? `${value.slice(0, limit - 3).trimEnd()}...` : value;
};

const truncatedName = computed(() => truncate(props.cardItem.name, 80));
const truncatedDescription = computed(() => truncate(props.cardItem.description, 150));

const cardMotion = {
    initial: {
        opacity: 0.5,
        y: 30,
        scale: 0.97,
    },
    animate: {
        opacity: 1,
        y: 0,
        scale: 1,
        transition: {
            delay: 0.5,
            duration: 0.5,
            ease: 'easeOut',
        },
    },
} as const;

</script>
