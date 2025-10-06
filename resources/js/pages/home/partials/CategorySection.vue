<template>
    <section class="mb-12">
        <!-- Category title -->
        <h2 class="text-xl font-bold text-gray-900 mb-6">
            {{ categoryName }}
        </h2>

        <!-- Partner categories grid -->
        <div class="grid grid-cols-3 lg:grid-cols-4 gap-4">
            <PartnerCategoryCard v-for="partnerCategory in partnerCategories" :key="partnerCategory.id"
                :partner-category="partnerCategory" />
        </div>
    </section>
</template>

<script setup lang="ts">
import PartnerCategoryCard from './PartnerCategoryCard.vue';
import { PartnerCategory } from '@/types/database';

interface Props {
    categoryName: string;
    partnerCategories: PartnerCategory[];
}

const props = defineProps<Props>();
</script>
