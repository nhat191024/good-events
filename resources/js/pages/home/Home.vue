<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons.vue';
import CategorySection from './partials/CategorySection.vue';
import Footer from './partials/Footer.vue';
import { PartnerCategory } from '@/types/database';
import { createSearchFilter } from '@/lib/search-filter';

interface Props {
    eventCategories: PartnerCategory[];
    partnerCategories: { [key: number]: PartnerCategory[] };
}

const props = defineProps<Props>();

const search = ref('');

const filteredPartnerCategories = computed(() => {
    if (!search.value.trim()) return props.partnerCategories
    const filter = createSearchFilter(['name', 'slug'], search.value.trim())
    const result: Record<number, PartnerCategory[]> = {}
    for (const catId in props.partnerCategories) {
        const arr = props.partnerCategories[catId].filter(filter)
        if (arr.length) result[Number(catId)] = arr
    }
    return result
})
</script>

<template>

    <Head class="font-lexend" title="Trang chủ - Sukientot" />

    <ClientAppHeaderLayout :background-class-names="'bg-primary-100'">

        <HeroBanner v-model="search" />
        <PartnerCategoryIcons />
        <!-- Partner Categories Sections -->
        <div class="container mx-auto px-4 py-8 space-y-12">
            <!-- Loop through event categories and display partner categories for each -->
            <CategorySection v-for="eventCategory in eventCategories" :key="eventCategory.id"
                :category-name="eventCategory.name"
                :partner-categories="filteredPartnerCategories[eventCategory.id] || []" />
        </div>
    </ClientAppHeaderLayout>
</template>
