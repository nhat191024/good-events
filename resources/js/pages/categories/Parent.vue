<script setup lang="ts">
import { ref, computed } from 'vue';
import SearchBar from './partials/SearchBar.vue';
import SubcategoryBlock from './partials/SubcategoryBlock.vue';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { createSearchFilter } from '@/lib/search-filter';
import { Head } from '@inertiajs/vue3';

interface PartnerCategory {
    id: number;
    name: string;
    slug: string;
    min_price: number | null;
    max_price: number | null;
    image: string | null;
}

interface ChildCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    partner_categories: PartnerCategory[];
}

interface ParentCategory {
    id: number;
    name: string;
    slug: string;
    description: string | null;
}

interface Props {
    parent: ParentCategory;
    children: ChildCategory[];
    filters: { q: string };
}

const props = defineProps<Props>();

const placeholderAvatar = (text: string) =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(text)}&background=ED3B50&color=ffffff&rounded=true&size=128`;

const q = ref(props.filters?.q ?? '')

const filteredChildren = computed(() => {
    const term = q.value.trim()
    if (!term) return props.children

    const filter = createSearchFilter(['name', 'slug'], term)

    return props.children
        .map((child) => {
            const filteredPartners = child.partner_categories.filter(filter)
            return { ...child, partner_categories: filteredPartners }
        })
        .filter((child) => child.partner_categories.length > 0)
})

</script>

<template>
    <Head title="Xem danh mục - Sự kiện" />
    <ClientHeaderLayout>
        <!-- Top bar: tiêu đề + ô tìm kiếm -->
        <div class="bg-transparent shadow-sm border border-gray-100 rounded-xl mx-auto my-0 px-2 py-0 w-full md:w-[80%] lg:w-[40%]">
            <SearchBar v-model="q" :showSearchBtn="false" />
        </div>

        <!-- Nội dung: lặp các danh mục con -->
        <div class=" mx-auto px-4 py-6">
            <div v-if="children.length === 0" class="text-gray-500">Chưa có danh mục con.</div>

            <div class="space-y-10">
                <SubcategoryBlock v-for="child in filteredChildren" :key="child.id" :title="child.name" :items="child.partner_categories.map(pc => ({
                    id: pc.id,
                    name: pc.name,
                    slug: pc.slug,
                    image: pc.image || placeholderAvatar(pc.name)
                }))" />
            </div>
        </div>
    </ClientHeaderLayout>
</template>
