<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

export type MenuItem = {
    label: string;
    route?: () => string;
    href?: () => string; // use this if navigating to an external link or a page route that does not use Inertia to render
    icon: Component;
    hoverClass?: string;
    textClass?: string;
};

const props = defineProps<{
    items: MenuItem[];
    hoverClass?: string;
}>();

const baseClasses =
    'first:pt-3 last:pb-3 first:rounded-t-lg flex w-full items-center px-4 py-2 text-sm transition-colors';

const iconClasses = 'w-5 h-5 mr-3 text-gray-400';

const getClasses = (item: MenuItem) => [
    baseClasses,
    item.textClass ?? 'text-gray-700',
    item.hoverClass ?? props.hoverClass ?? 'hover:bg-gray-100',
];
</script>

<template>
    <template v-for="(item, index) in items" :key="index">
        <!-- Use Inertia Link for route property -->
        <Link v-if="item.route" :href="item.route()" :class="getClasses(item)">
            <component :is="item.icon" :class="iconClasses" />
            {{ item.label }}
        </Link>

        <!-- Use regular anchor tag for href property -->
        <a v-else-if="item.href" :href="item.href()" :class="getClasses(item)">
            <component :is="item.icon" :class="iconClasses" />
            {{ item.label }}
        </a>
    </template>
</template>
