<template>
    <header class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
        <div class="space-y-3">
            <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
                <ul class="flex items-center gap-2">
                    <li>
                        <Link :href="route(breadcrumbRouteName)" class="hover:text-primary-800">
                            {{ breadcrumbLabel }}
                        </Link>
                    </li>
                    <template v-if="isCategoryPage">
                        <li aria-hidden="true" class="text-primary-400">›</li>
                        <li class="text-primary-800">
                            {{ categoryName }}
                        </li>
                    </template>
                </ul>
            </nav>

            <div class="space-y-2">
                <h1 class="text-3xl font-semibold leading-tight text-gray-900 sm:text-4xl">
                    {{ headingText }}
                </h1>
                <p class="max-w-2xl text-sm text-gray-600">
                    {{ subHeadingText }}
                </p>
                <p class="text-xs font-semibold uppercase tracking-wider text-primary-500">
                    {{ totalItems }} bài viết
                </p>
            </div>
        </div>

        <div class="h-min w-full max-w-xl">
            <LargeSearchBar
                :model-value="searchTerm"
                :placeholder="searchPlaceholder"
                @update:modelValue="emit('update:searchTerm', $event)"
                @search="emit('search', $event)"
            />
        </div>
    </header>
</template>

<script setup lang="ts">
import { toRefs } from 'vue';
import { Link } from '@inertiajs/vue3';
import LargeSearchBar from '@/components/LargeSearchBar.vue';
import { inject } from "vue";

const route = inject('route') as any;

interface Props {
    isCategoryPage: boolean;
    categoryName?: string | null;
    headingText: string;
    subHeadingText: string;
    searchTerm: string;
    totalItems: number;
    searchPlaceholder?: string;
    breadcrumbLabel?: string;
    breadcrumbRouteName?: string;
}

const props = withDefaults(defineProps<Props>(), {
    categoryName: null,
    searchPlaceholder: 'Tìm kiếm địa điểm tổ chức sự kiện...',
    breadcrumbLabel: 'Blog địa điểm',
    breadcrumbRouteName: 'blog.discover',
});

const emit = defineEmits<{
    'update:searchTerm': [value: string];
    search: [value: string];
}>();

const { isCategoryPage, categoryName, headingText, subHeadingText, searchTerm, totalItems, searchPlaceholder, breadcrumbLabel, breadcrumbRouteName } = toRefs(props);
</script>
