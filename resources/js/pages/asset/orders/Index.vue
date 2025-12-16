<template>
    <OrdersPage v-bind="props" />
</template>

<script setup lang="ts">
import OrdersPage from './Page.vue';
import type { AssetOrdersPageProps } from './types';

const props = defineProps<AssetOrdersPageProps>();
</script>
