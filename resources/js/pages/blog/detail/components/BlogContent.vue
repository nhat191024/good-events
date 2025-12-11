<template>
    <section class="flex flex-col gap-0 md:gap-6">

        <p v-if="blog.excerpt" class="text-lg font-medium text-gray-700">
            {{ blog.excerpt }}
        </p>

        <div class="prose prose-lg max-w-none text-gray-700" v-html="blog.content" />

        <div v-if="blog.latitude && blog.longitude"
            class="mt-8 rounded-3xl border border-gray-100 bg-white p-0 md:p-6 shadow-sm">
            <h3 class="mb-4 text-lg font-bold text-gray-900">Bản đồ địa điểm</h3>
            <a :href="`https://www.google.com/maps/search/?api=1&query=${blog.latitude},${blog.longitude}`"
                target="_blank" rel="noopener noreferrer"
                class="group relative block aspect-video w-full overflow-hidden rounded-2xl">
                <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0"
                    :src="`https://maps.google.com/maps?q=${blog.latitude},${blog.longitude}&hl=vi&z=14&output=embed`"
                    class="pointer-events-none h-full w-full border-0"></iframe>
                <div
                    class="absolute inset-0 flex items-center justify-center bg-black/10 transition group-hover:bg-black/20">
                    <span
                        class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-primary-600 shadow-lg transition group-hover:scale-105">
                        Xem trên Google Maps
                    </span>
                </div>
            </a>
            <p v-if="blog.address" class="mt-4 flex items-start gap-2 text-sm text-gray-600">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                    stroke="currentColor" class="mt-0.5 h-5 w-5 shrink-0 text-primary-500">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                </svg>
                {{ blog.address }}
            </p>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import type { BlogDetail } from '../../types';

const props = defineProps<{ blog: BlogDetail }>();

const isGoodLocation = computed(() => props.blog.type === 'good_location');

const locationLabel = computed(() => {
    if (!isGoodLocation.value) return '';
    const location = props.blog.location;
    if (!location?.name) return '';

    if (location.province?.name && location.province.name !== location.name) {
        return `${location.name}, ${location.province.name}`;
    }

    return location.name;
});

const capacityLabel = computed(() => {
    if (!isGoodLocation.value) return '';
    const capacity = props.blog.max_people;
    if (capacity === null || capacity === undefined) return '';

    return `${new Intl.NumberFormat('vi-VN').format(capacity)} khách`;
});

const showLocationMeta = computed(() => Boolean(locationLabel.value || capacityLabel.value));
</script>
