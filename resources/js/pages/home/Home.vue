<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import Header from '@/components/home/Header.vue';
import HeroBanner from '@/components/home/HeroBanner.vue';
import CategoryIcons from '@/components/home/CategoryIcons.vue';
import CategorySection from '@/components/home/CategorySection.vue';
import Footer from '@/components/home/Footer.vue';

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
</script>

<template>
    <Head title="Trang chủ - Sukientot" />
    
    <div class="min-h-screen bg-white">
        <Header />
        
        <main>
            <HeroBanner />
            <CategoryIcons :categories="rootCategories" />
            
            <!-- Partner Categories Sections -->
            <div class="container mx-auto px-4 py-8 space-y-12">
                <!-- Loop through event categories and display partner categories for each -->
                <CategorySection 
                    v-for="eventCategory in eventCategories" 
                    :key="eventCategory.id"
                    :category-name="eventCategory.name"
                    :partner-categories="partnerCategories[eventCategory.id] || []"
                />
            </div>
            
        </main>
        
        <Footer/>
    </div>
</template>
