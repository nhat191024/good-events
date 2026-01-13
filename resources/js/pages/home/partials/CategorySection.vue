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
        <motion.div
            class="flex items-end justify-between mb-6 gap-3 flex-wrap"
            :initial="headingMotion.initial"
            :while-in-view="headingMotion.visible"
            :viewport="headingMotion.viewport"
            :transition="headingMotion.transition"
        >
            <Link :href="seeMoreHref" class="text-xl font-bold text-gray-900 border-b border-transparent hover:border-primary-500 border-b-3">
                {{ categoryName }}
            </Link>
            <Link :href="seeMoreHref"
                class="text-sm font-medium text-primary-600 hover:text-primary-700">
                Xem thêm
            </Link>
        </motion.div>

        <!-- Partner categories grid -->
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 md:gap-4 lg:gap-6 gap-2">
            <PartnerCategoryCard v-for="partnerCategory in partnerCategories" :key="partnerCategory.id"
                :partner-category="partnerCategory" />
        </div>
    </motion.section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import PartnerCategoryCard from './PartnerCategoryCard.vue';
import { PartnerCategory } from '@/types/database';
import { motion } from 'motion-v';
import { inject } from "vue";

const route = inject('route') as any;

interface Props {
    categoryName: string;
    categorySlug: string;
    partnerCategories: PartnerCategory[];
    hasMoreChildren?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    hasMoreChildren: false,
});

const seeMoreHref = computed(() =>
    route('home.category', { category_slug: props.categorySlug })
);

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
