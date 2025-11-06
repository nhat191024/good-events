<template>
    <Link :href="routeHref" class="block h-full">
        <div
            class="card bg-base-100 shadow-sm group relative overflow-hidden cursor-pointer rounded-md">
            <figure class="relative aspect-[4/3]">
                <img
                    :src="currentImage"
                    :alt="cardItem.name"
                    loading="lazy"
                    class="w-full h-full object-cover lazy-image"
                    @error="handleImageError" />
                <div v-if="showInfo" class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            </figure>
            <div v-if="showInfo"
                class="card-body absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-300 text-white flex flex-col justify-center p-4 overflow-scroll scrollbar-hide">
                <div class="overflow-y-scroll">
                    <p class="text-[0.75rem] sm:text-[0.80rem] md:text-[1rem]">
                        <span class="font-semibold block">{{ truncatedName }}</span>
                    </p>
                </div>
                <div class="card-actions justify-end mt-auto pt-2"></div>
            </div>
        </div>
    </Link>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import { Props } from './types';
import { getImg } from '@/pages/booking/helper';

const props = withDefaults(defineProps<Props>(),{
    showInfo: true
});

const placeholderImage = getImg(null);
const currentImage = ref(getImg(props.cardItem.image));

watch(
    () => props.cardItem.image,
    (newImage) => {
        currentImage.value = getImg(newImage);
    }
);

const truncate = (value: string | null | undefined, limit: number) => {
    if (!value) return '';
    return value.length > limit ? `${value.slice(0, limit - 3).trimEnd()}...` : value;
};

const truncatedName = computed(() => truncate(props.cardItem.name, 80));
const truncatedDescription = computed(() => truncate(props.cardItem.description, 150));

const handleImageError = (event: Event) => {
    const target = event.target as HTMLImageElement | null;
    if (!target) return;
    target.onerror = null;
    currentImage.value = placeholderImage;
};

</script>
