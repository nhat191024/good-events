<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Props { }

const props = defineProps<Props>();

const categories = [
    {
        id: 1,
        name: 'Sự kiện',
        slug: 'su-kien',
        icon: 'mdi:flower',
        image: '/images/logo-su-kien.png',
    },
    {
        id: 2,
        name: 'Tài liệu',
        slug: 'tai-lieu',
        icon: 'mdi:book-open',
        image: '/images/logo-tai-lieu.png',
    },
    {
        id: 3,
        name: 'Thiết bị',
        slug: 'tim-khach-san',
        icon: 'mdi:bed',
        image: '/images/logo-loa-dai.png',
    },
    {
        id: 4,
        name: 'Khách sạn',
        slug: 'khach-san',
        icon: 'mdi:bag-personal',
        image: '/images/logo_khach_san.png',
    },
];

const getIconForCategory = (slug: string) => {
    const category = categories.find(c => c.slug === slug);
    return category ? category.icon : '';
};

const getImageForCategory = (slug: string) => {
    const category = categories.find(c => c.slug === slug);
    return category?.image || '';
};
</script>
<template>
    <section class="w-full py-8 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex flex-wrap justify-center items-center gap-8 md:gap-16">
                <Link v-for="category in categories" :key="category.id" :href="`/categories/${category.slug}`"
                    class="flex flex-col items-center group cursor-pointer transition-transform hover:scale-105">
                <div
                    class="w-16 h-16 md:w-15 md:h-15 bg-white ring-1 ring-gray-200 rounded-full flex items-center justify-center overflow-hidden mb-3">
                    <img :src="getImageForCategory(category.slug)" :alt="category.name"
                        class="w-12 h-12 object-contain" />
                </div>
                <span
                    class="text-sm md:text-base text-gray-700 font-medium text-center group-hover:text-red-600 transition-colors">
                    {{ category.name }}
                </span>
                </Link>
            </div>
        </div>
    </section>
</template>
