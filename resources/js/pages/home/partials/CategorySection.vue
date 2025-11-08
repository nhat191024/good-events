<template>
    <motion.section
        class="mb-12"
        v-if="partnerCategories.length>0"
        :initial="sectionMotion.initial"
        :while-in-view="sectionMotion.visible"
        :viewport="sectionMotion.viewport"
        :transition="sectionMotion.transition"
    >
        <!-- Category title -->
        <motion.h2
            class="text-xl font-bold text-gray-900 mb-6"
            :initial="headingMotion.initial"
            :while-in-view="headingMotion.visible"
            :viewport="headingMotion.viewport"
            :transition="headingMotion.transition"
        >
            {{ categoryName }}
        </motion.h2>

        <!-- Partner categories grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 md:gap-4 lg:gap-6 gap-2">
            <PartnerCategoryCard v-for="partnerCategory in partnerCategories" :key="partnerCategory.id"
                :partner-category="partnerCategory" />
        </div>
    </motion.section>
</template>

<script setup lang="ts">
import PartnerCategoryCard from './PartnerCategoryCard.vue';
import { PartnerCategory } from '@/types/database';
import { motion } from 'motion-v';

interface Props {
    categoryName: string;
    partnerCategories: PartnerCategory[];
}

const props = defineProps<Props>();

const sectionMotion = {
    initial: { opacity: 0.5, y: 30 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.6, ease: 'easeOut' },
    viewport: { once: true, amount: 0.25 },
} as const;

const headingMotion = {
    initial: { opacity: 0.5, y: 16 },
    visible: { opacity: 1, y: 0 },
    transition: { duration: 0.5, ease: 'easeOut', delay: 0.1 },
    viewport: { once: true, amount: 0.4 },
} as const;
</script>
