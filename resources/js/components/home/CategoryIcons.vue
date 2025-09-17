<script setup lang="ts">
interface Category {
    id: number;
    name: string;
    slug: string;
    parent_id: number | null;
    description: string | null;
}

interface Props {
    categories: Category[];
}

const props = defineProps<Props>();

// Icon mapping for different categories - using placeholder icons
const getIconForCategory = (slug: string) => {
    const iconMap: { [key: string]: string } = {
        'su-kien': '🎉',
        'tai-lieu': '📋',
        'tim-khach-san': '🏨',
        'vat-tu': '🔧',
        // Add more mappings as needed
    };
    return iconMap[slug] || '📁';
};
</script>

<template>
    <section class="py-8 bg-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-center items-center gap-8 md:gap-16">
                <div 
                    v-for="category in categories" 
                    :key="category.id"
                    class="flex flex-col items-center group cursor-pointer transition-transform hover:scale-105"
                >
                    <!-- Icon Container -->
                    
                    <div class="w-16 h-16 md:w-15 md:h-15 bg-red-500 rounded-full flex items-center justify-center mb-3">
                            <!-- Placeholder icon - replace with actual icons -->
                            <span class="text-white text-lg md:text-xl">
                                {{ getIconForCategory(category.slug) }}
                            </span>
                        </div>
                    
                    <!-- Category Name -->
                    <span class="text-sm md:text-base text-gray-700 font-medium text-center group-hover:text-red-600 transition-colors">
                        {{ category.name }}
                    </span>
                </div>
            </div>
        </div>
    </section>
</template>

<style scoped>
/* Additional custom styles if needed */
</style>