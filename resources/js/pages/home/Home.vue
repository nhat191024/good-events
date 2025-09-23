<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Header from './partials/Header.vue';
import HeroBanner from './partials/HeroBanner.vue';
import CategoryIcons from './partials/CategoryIcons.vue';
import CategorySection from './partials/CategorySection.vue';
import Footer from './partials/Footer.vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    description: string | null;
}

interface PartnerCategory {
    id: number;
    name: string;
    slug: string;
    category_id: number;
    min_price: number;
    max_price: number;
    description: string | null;
}

interface Props {
    rootCategories: Category[];
    eventCategories: Category[];
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
            <HeroBanner />
            <CategoryIcons :categories="rootCategories" />
            <!-- Search bar -->
            <div class="container mx-auto px-4 mt-4 mb-2">
              <input
                v-model="search"
                type="text"
                placeholder="Tìm kiếm đối tác..."
                class="w-full border rounded-lg px-4 py-2 text-base focus:ring-2 focus:ring-[#ED3B50]"
              />
            </div>
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
