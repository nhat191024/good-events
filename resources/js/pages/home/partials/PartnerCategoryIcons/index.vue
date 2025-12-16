<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { computed } from 'vue';
import { PartnerCategoryItems as Category } from './type';
import { Link } from '@inertiajs/vue3';
import { motion } from 'motion-v';

interface Props {
    categories: Category[]
}

const props = defineProps<Props>();

const categories = computed<Category[]>(() => props.categories);

const getImageForCategory = (image: string) => {
    // const category = categories.value.find(c => c.slug === slug);
    return getImg(image);
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
<template>
    <section class="w-full md:py-4 py-0 bg-white">
        <motion.div :initial="iconsMotion.initial" :animate="iconsMotion.animate">
            <div class="container mx-auto px-4">
                <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 justify-items-center gap-y-3">
                    <Link v-for="category in categories" :key="category.id" :href="category.href"
                        class="flex flex-col items-center group cursor-pointer transition-transform hover:scale-105">
                        <div
                            class="w-16 h-16 md:w-15 md:h-15 bg-white ring-1 ring-gray-200 rounded-full flex items-center justify-center overflow-hidden mb-3">
                            <img :src="getImageForCategory(category.image ?? '')" :alt="category.name"
                                class="w-12 h-12 object-cover rounded-full" />
                        </div>
                        <span
                            class="text-sm md:text-base text-gray-700 font-medium text-center group-hover:text-red-600 transition-colors">
                            {{ category.name }}
                        </span>
                    </Link>
                </div>
            </div>
        </motion.div>
    </section>
</template>
