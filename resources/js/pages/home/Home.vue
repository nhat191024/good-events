<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Header from './partials/Header.vue';
import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons.vue';
import CategorySection from './partials/CategorySection.vue';
import Footer from './partials/Footer.vue';
import { PartnerCategory } from '@/types/database';

interface Props {
    eventCategories: PartnerCategory[];
    partnerCategories: { [key: number]: PartnerCategory[] };
}

const props = defineProps<Props>();

const search = ref('');

const filteredPartnerCategories = computed(() => {
  if (!search.value.trim()) return props.partnerCategories;
  const result: { [key: number]: PartnerCategory[] } = {};
  for (const catId in props.partnerCategories) {
    const arr = props.partnerCategories[catId].filter(pc =>
      pc.name.toLowerCase().includes(search.value.trim().toLowerCase())
    );
    if (arr.length) result[catId] = arr;
  }
  return result;
});
</script>

<template>
    <Head title="Trang chủ - Sukientot" />

    <div class="min-h-screen bg-white">
        <Header />

        <main>
            <HeroBanner v-model="search" />
            <PartnerCategoryIcons/>
            <!-- Partner Categories Sections -->
            <div class="container mx-auto px-4 py-8 space-y-12">
                <!-- Loop through event categories and display partner categories for each -->
                <CategorySection
                    v-for="eventCategory in eventCategories"
                    :key="eventCategory.id"
                    :category-name="eventCategory.name"
                    :partner-categories="filteredPartnerCategories[eventCategory.id] || []"
                />
            </div>

        </main>

        <Footer/>
    </div>
</template>
