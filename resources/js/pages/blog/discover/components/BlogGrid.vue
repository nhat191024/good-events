<template>
    <div v-if="hasBlogs" class="flex flex-col gap-8">
        <div class="grid gap-6 lg:grid-cols-3">
            <BlogCard
                v-if="featured"
                :key="featured.id"
                :blog="featured"
                variant="featured"
                class="lg:col-span-2"
                :detail-route-name="detailRouteName"
            />

            <div class="flex flex-col gap-6" v-if="secondary.length">
                <BlogCard
                    v-for="item in secondary"
                    :key="item.id"
                    :blog="item"
                    variant="secondary"
                    :detail-route-name="detailRouteName"
                />
            </div>
            <div v-else class="hidden lg:block" />
        </div>

        <div v-if="remainder.length" class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <BlogCard
                v-for="item in remainder"
                :key="item.id"
                :blog="item"
                variant="compact"
                :detail-route-name="detailRouteName"
            />
        </div>
    </div>
    <div v-else class="rounded-2xl border border-dashed border-gray-200 bg-gray-50 py-16 text-center text-sm text-gray-500">
        Hiện chưa có bài viết nào.
    </div>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import BlogCard from './BlogCard.vue';
import type { BlogSummary } from '../../types';

const props = withDefaults(defineProps<{ blogs?: BlogSummary[]; detailRouteName?: string }>(), {
    blogs: () => [],
    detailRouteName: 'blog.show',
});

const hasBlogs = computed(() => props.blogs.length > 0);

const featured = computed(() => props.blogs[0] ?? null);
const secondary = computed(() => props.blogs.slice(1, 3));
const remainder = computed(() => props.blogs.slice(3));
</script>
