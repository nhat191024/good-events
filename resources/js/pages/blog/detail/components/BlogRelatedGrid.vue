<template>
    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-semibold text-gray-900">Bài viết liên quan</h2>
            <Link :href="route('blog.discover')" class="text-sm font-medium text-primary-600 hover:text-primary-700">
                Xem tất cả
            </Link>
        </div>

        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <BlogCard
                v-for="item in blogs"
                :key="item.id"
                :blog="item"
                variant="compact"
            />
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import BlogCard from '../../discover/components/BlogCard.vue';
import type { BlogSummary } from '../../types';

defineProps<{ blogs: BlogSummary[] }>();
</script>

