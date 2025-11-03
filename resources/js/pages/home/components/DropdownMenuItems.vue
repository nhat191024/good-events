<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import type { Component } from 'vue';

type MenuItem = {
    label: string;
    route: () => string;
    icon: Component;
    external?: boolean;
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
        <Link v-if="!item.external" :href="item.route()" :class="getClasses(item)">
            <component :is="item.icon" :class="iconClasses" />
            {{ item.label }}
        </Link>

        <a v-else :href="item.route()" :class="getClasses(item)">
            <component :is="item.icon" :class="iconClasses" />
            {{ item.label }}
        </a>
    </template>
</template>
