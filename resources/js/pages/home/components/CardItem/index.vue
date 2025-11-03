<template>
    <Link :href="routeHref" class="block">
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
            <!-- Placeholder image -->
            <div class="aspect-square bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center">
                <img :src="getImg(cardItem.image)"
                    :alt="cardItem.name" class="w-full h-full object-cover" />
            </div>
        </div>
        <!-- Content -->
        <div class="p-4">
            <h3 class="font-semibold text-gray-900 text-md mb-1">
                {{ cardItem.name }}
            </h3>
        </div>
    </div>
    </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Props } from './types';
import { getImg } from '@/pages/booking/helper';

const props = defineProps<Props>();

console.log(props);

</script>
