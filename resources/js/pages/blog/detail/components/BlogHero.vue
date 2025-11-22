<template>
    <article class="grid gap-8 lg:grid-cols-[minmax(0,2fr)_minmax(0,3fr)]">
        <div class="space-y-4">
            <div class="flex flex-wrap items-center gap-3 text-sm font-medium">
                <Link
                    v-if="categoryLink"
                    :href="categoryLink"
                    class="inline-flex items-center gap-2 rounded-full bg-primary-100 px-4 py-2 text-primary-700 transition hover:bg-primary-200"
                >
                    {{ blog.category?.name }}
                </Link>
                <span v-else class="inline-flex items-center gap-2 rounded-full bg-gray-100 px-4 py-2 text-gray-600">
                    {{ blog.category?.name ?? 'Blog sự kiện' }}
                </span>
            </div>

            <h1 class="text-3xl font-semibold leading-tight text-gray-900 sm:text-4xl">
                {{ blog.title }}
            </h1>

            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-500">
                <span v-if="blog.published_human || blog.published_at">
                    {{ blog.published_human ?? formattedPublishedDate }}
                </span>
                <span v-if="blog.read_time_minutes" aria-hidden="true">•</span>
                <span v-if="blog.read_time_minutes">
                    {{ blog.read_time_minutes }} phút đọc
                </span>
                <span v-if="blog.author?.name" aria-hidden="true">•</span>
                <span v-if="blog.author?.name">Bởi {{ blog.author.name }}</span>
            </div>
            <div v-if="showLocationMeta" class="flex flex-wrap items-center gap-3 text-sm text-primary-800">
                <span
                    v-if="locationLabel"
                    class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 font-semibold"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="h-4 w-4 text-primary-500">
                        <path d="M12 2.25c-3.728 0-6.75 3.022-6.75 6.75 0 5.062 5.492 11.159 6.33 12.036a.75.75 0 0 0 1.04.038l.038-.038c.838-.877 6.34-6.974 6.34-12.036 0-3.728-3.022-6.75-6.75-6.75m0 9.563a2.813 2.813 0 1 1 0-5.625 2.813 2.813 0 0 1 0 5.625" />
                    </svg>
                    <span>{{ locationLabel }}</span>
                </span>
                <span
                    v-if="capacityLabel"
                    class="inline-flex items-center gap-2 rounded-full bg-primary-50 px-3 py-1 font-semibold"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-4 w-4 text-primary-500">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-6-6h12" />
                    </svg>
                    <span>{{ capacityLabel }}</span>
                </span>
            </div>

            <div v-if="hasTags" class="flex flex-wrap gap-2">
                <span v-for="tag in displayTags" :key="tag.slug" class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-600">
                    #{{ tag.name }}
                </span>
            </div>
        </div>

        <div class="overflow-hidden rounded-3xl bg-gray-100 shadow-sm">
            <img
                v-if="blog.thumbnail"
                :src="blog.thumbnail"
                :alt="blog.title"
                class="h-full w-full max-h-[420px] object-cover"
            />
            <div v-else class="flex h-full min-h-[280px] w-full items-center justify-center bg-gradient-to-br from-slate-700 via-slate-800 to-slate-900 text-white">
                Không có hình ảnh
            </div>
        </div>
    </article>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import type { BlogDetail } from '../../types';

const props = withDefaults(defineProps<{ blog: BlogDetail; categoryRouteName?: string }>(), {
    categoryRouteName: 'blog.category',
});

const categoryLink = computed(() => {
    const categorySlug = props.blog.category?.slug;
    if (!categorySlug) return null;
    return route(props.categoryRouteName, { category_slug: categorySlug });
});

const displayTags = computed(() => props.blog.tags ?? []);
const hasTags = computed(() => displayTags.value.length > 0);

const formattedPublishedDate = computed(() => {
    if (!props.blog.published_at) return '';
    try {
        return new Intl.DateTimeFormat('vi-VN', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
        }).format(new Date(props.blog.published_at));
    } catch (error) {
        return props.blog.published_at;
    }
});

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
