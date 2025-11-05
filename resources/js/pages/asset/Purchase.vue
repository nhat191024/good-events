<template>
    <PurchasePage v-bind="props" />
</template>

<script setup lang="ts">
import PurchasePage, { type PurchasePageProps } from './purchase/index.vue';

const props = defineProps<PurchasePageProps>();
</script>
