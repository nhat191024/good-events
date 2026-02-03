<template>
    <section class="flex flex-col gap-6 rounded-2xl border border-gray-100 bg-gray-50/40 p-4 sm:p-6">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-700">Danh mục nổi bật:</span>
            <Link
                v-for="category in categoryOptions"
                :key="category.slug ?? category.id"
                :href="category.slug ? route('asset.category', { category_slug: category.slug }) : '#'"
                class="rounded-full border border-primary-100 bg-white px-3 py-1 text-xs font-medium text-primary-700 transition hover:border-primary-300 hover:text-primary-900"
            >
                {{ category.name }}
            </Link>
            <span v-if="!categoryOptions.length" class="text-sm text-gray-500">Chưa có danh mục hiển thị.</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-gray-700">Từ khóa phổ biến:</span>

            <Button
                v-for="tag in tagOptions"
                :key="tag.slug ?? tag.id"
                :variant="selectedTags.includes(tag.slug ?? '') ? 'default' : 'outline'"
                size="sm"
                :class="cn(
                    'rounded-full border border-primary-100 text-xs font-medium',
                    selectedTags.includes(tag.slug ?? '')
                        ? 'text-white'
                        : 'text-primary-700 bg-white'
                )"
                @click="handleToggle(tag.slug)"
            >
                {{ tag.name }}
            </Button>

            <span v-if="!tagOptions.length" class="text-sm text-gray-500">Đang cập nhật...</span>

            <Button
                v-if="hasActiveFilters"
                variant="ghost"
                size="sm"
                class="ml-auto text-xs text-primary-600 hover:text-primary-700"
                @click="emit('reset')"
            >
                Xoá bộ lọc
            </Button>
        </div>

        <div
            v-if="selectedTagItems.length"
            class="flex flex-wrap items-center gap-2 rounded-xl border border-primary-100 bg-primary-10 px-3 py-2"
        >
            <span class="text-xs font-semibold uppercase text-primary-700">Đang lọc:</span>
            <Button
                v-for="tag in selectedTagItems"
                :key="tag.slug"
                variant="outline"
                size="sm"
                class="flex items-center gap-2 border-primary-200 text-xs font-medium text-primary-700 hover:border-primary-300 hover:text-primary-900"
                @click="emit('removeTag', tag.slug)"
            >
                <span>{{ tag.name }}</span>
                <span aria-hidden="true">x</span>
            </Button>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { cn } from '@/lib/utils';
import type { Category, Tag } from '@/pages/home/types';

import { inject } from "vue";

const route = inject('route') as any;

interface SelectedTagItem {
    slug: string;
    name: string;
}

interface Props {
    categoryOptions: Category[];
    tagOptions: Tag[];
    selectedTags: string[];
    selectedTagItems: SelectedTagItem[];
    hasActiveFilters: boolean;
}

defineProps<Props>();

const emit = defineEmits<{
    toggleTag: [slug: string];
    reset: [];
    removeTag: [slug: string];
}>();

const handleToggle = (slug: string | null | undefined) => {
    if (!slug) return;
    emit('toggleTag', slug);
};
</script>
