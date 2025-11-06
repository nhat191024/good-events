<template>
    <RentDiscoverPage v-bind="props" />
</template>

<script setup lang="ts">
import RentDiscoverPage, { type DiscoverPageProps } from './discover/index.vue';

const props = defineProps<DiscoverPageProps>();
</script>
