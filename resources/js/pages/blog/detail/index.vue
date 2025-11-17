<template>
    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <section class="bg-white pb-16 pt-6 w-full">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-10 px-4 sm:px-6 lg:px-8">
                <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li>
                            <Link :href="route('blog.discover')" class="hover:text-primary-800">Blog địa điểm</Link>
                        </li>
                        <li v-if="categorySlug" class="flex items-center gap-2">
                            <span aria-hidden="true" class="text-primary-400">›</span>
                            <Link :href="route('blog.category', { category_slug: categorySlug })" class="hover:text-primary-800">
                                {{ props.blog.category?.name || 'Danh mục' }}
                            </Link>
                        </li>
                        <li class="flex items-center gap-2">
                            <span aria-hidden="true" class="text-primary-400">›</span>
                            <span class="text-primary-800">Chi tiết bài viết</span>
                        </li>
                    </ul>
                </nav>

                <BlogHero :blog="props.blog" />

                <BlogContent :blog="props.blog" />

                <BlogRelatedGrid v-if="relatedBlogs.length" :blogs="relatedBlogs" />
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import BlogContent from './components/BlogContent.vue';
import BlogHero from './components/BlogHero.vue';
import BlogRelatedGrid from './components/BlogRelatedGrid.vue';

import type { BlogDetail, BlogSummary } from '../types';

type RelatedPayload =
    | BlogSummary[]
    | { data?: BlogSummary[] | null }
    | Paginated<BlogSummary>
    | undefined;

export interface DetailPageProps {
    blog: BlogDetail;
    related?: RelatedPayload;
}

const props = withDefaults(defineProps<DetailPageProps>(), {
    related: undefined,
});

const pageTitle = computed(() => `${props.blog.title} - Blog địa điểm Sukientot`);
const categorySlug = computed(() => props.blog.category?.slug ?? null);

function toArray<T>(input: RelatedPayload | { data?: T[] | null } | T[] | undefined): T[] {
    if (!input) return [];
    if (Array.isArray(input)) return input;
    if ('data' in input) {
        const data = input.data;
        return Array.isArray(data) ? data : [];
    }
    return [];
}

const relatedBlogs = computed(() => toArray<BlogSummary>(props.related));

export type { DetailPageProps };
</script>

