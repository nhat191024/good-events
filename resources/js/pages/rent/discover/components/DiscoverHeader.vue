<template>
    <header class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div class="space-y-2">
            <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
                <ul class="flex items-center gap-2">
                    <li>
                        <Link :href="route('rent.home')" class="hover:text-primary-800">
                            Kho thuê vật tư
                        </Link>
                    </li>
                    <li v-if="isCategoryPage" aria-hidden="true" class="text-primary-400">›</li>
                    <li v-if="isCategoryPage" class="text-primary-800">
                        {{ categoryName }}
                    </li>
                </ul>
            </nav>

            <h1 class="text-2xl font-semibold text-gray-900 sm:text-3xl">
                {{ headingText }}
            </h1>
            <p class="text-sm text-gray-600">
                {{ subHeadingText }}
            </p>
        </div>

        <div class="w-full max-w-xl md:w-auto">
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

interface Props {
    isCategoryPage: boolean;
    categoryName?: string | null;
    headingText: string;
    subHeadingText: string;
    searchTerm: string;
    searchPlaceholder?: string;
}

const props = withDefaults(defineProps<Props>(), {
    categoryName: null,
    searchPlaceholder: 'Tìm kiếm vật tư sự kiện...',
});

const emit = defineEmits<{
    'update:searchTerm': [value: string];
    search: [value: string];
}>();

const { isCategoryPage, categoryName, headingText, subHeadingText, searchTerm, searchPlaceholder } = toRefs(props);
</script>
