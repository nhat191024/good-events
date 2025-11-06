<script setup lang="ts">
import { ref, onMounted, onBeforeUnmount, type Ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

//! Waring: before using this component, please take note that the route? props (string) HAVE to match the route names on laravel side
interface MenuItem {
    name: string;
    slug: string;
    route?: string | null; // or if you only want to test, assign null
}

interface Props {
    menuItems: MenuItem[];
}

const props = withDefaults(defineProps<Props>(), {
    menuItems: () => []
});

const isOpen: Ref<boolean> = ref(false);
const menuRef: Ref<HTMLDivElement | null> = ref(null);

const toggleMenu = (): void => {
    isOpen.value = !isOpen.value;
};

const closeMenu = (): void => {
    isOpen.value = false;
};

const handleClickOutside = (event: MouseEvent): void => {
    if (menuRef.value && !menuRef.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});
</script>
<template>
    <div class="relative" ref="menuRef">
        <button type="button" aria-label="Mở menu" @click="toggleMenu"
            class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white shadow-md hover:shadow-lg transition">
            <span class="sr-only">Menu</span>
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" class="transition-transform duration-200"
                :class="{ 'rotate-90': isOpen }">
                <path d="M3 6h18M3 12h18M3 18h18" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
            </svg>
        </button>

        <transition enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="isOpen"
                class="absolute left-0 mt-2 w-64 rounded-lg shadow-lg bg-white ring-1 ring-primary-200 ring-opacity-5 z-100">
                <div class="py-1">
                    <Link v-for="item in menuItems" :key="item.slug" :href="item.route??'#'"
                        @click="closeMenu"
                        class="flex items-center px-4 py-3 text-sm text-gray-700 hover:bg-gray-100 transition-colors border-b border-gray-100 last:border-0">
                    <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                    <span class="font-medium">{{ item.name }}</span>
                    </Link>

                    <div v-if="menuItems.length === 0" class="px-4 py-6 text-center text-gray-500 text-sm">
                        Không có mục menu
                    </div>
                </div>
            </div>
        </transition>
    </div>
</template>
