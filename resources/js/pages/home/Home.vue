<script setup lang="ts">
import { ref, computed } from 'vue';
import { motion } from 'motion-v';
import { Head } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';
import CategorySection from './partials/CategorySection.vue';
import Footer from './partials/Footer.vue';
import { PartnerCategory } from '@/types/database';
import { createSearchFilter } from '@/lib/search-filter';

interface Props {
    eventCategories: PartnerCategory[];
    partnerCategories: { [key: number]: PartnerCategory[] };
}

const props = defineProps<Props>();

const categories = [
    {
        id: 1,
        name: 'Sự kiện',
        slug: 'su-kien',
        icon: 'mdi:flower',
        image: '/images/logo-su-kien.webp',
        href: route('home')
    },
    {
        id: 2,
        name: 'Tài liệu',
        slug: 'tai-lieu',
        icon: 'mdi:book-open',
        image: '/images/logo-tai-lieu.webp',
        href: route('asset.home')
    },
    {
        id: 3,
        name: 'Thiết bị',
        slug: 'tim-khach-san',
        icon: 'mdi:bed',
        image: '/images/logo-loa-dai.webp',
        href: route('rent.home')
    },
    {
        id: 4,
        name: 'Khách sạn',
        slug: 'khach-san',
        icon: 'mdi:bag-personal',
        image: route('blog.discover')
    },
];

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

const categoryMotions = computed(() => props.eventCategories.map((_, index) => ({
    initial: {
        opacity: 0.5,
        y: 35,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            delay: 0.5 + index * 0.1,
            duration: 0.6,
            ease: 'easeOut',
        },
    },
})))
</script>

<template>

    <Head class="font-lexend" title="Trang chủ - Sukientot" />

    <ClientAppHeaderLayout :background-class-names="'bg-primary-100'">

        <HeroBanner v-model="search" />
        
        <PartnerCategoryIcons :categories="categories" />
        
        <!-- Partner Categories Sections -->
        <div class="container mx-auto px-4 py-8 space-y-12">
            <!-- Loop through event categories and display partner categories for each -->
            <motion.div v-for="(eventCategory, index) in eventCategories" :key="eventCategory.id"
                :initial="categoryMotions[index]?.initial" :animate="categoryMotions[index]?.animate">
                <CategorySection :category-name="eventCategory.name"
                    :partner-categories="filteredPartnerCategories[eventCategory.id] || []" />
            </motion.div>
        </div>
    </ClientAppHeaderLayout>
</template>
