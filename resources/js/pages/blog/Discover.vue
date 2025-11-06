<template>
    <DiscoverPage v-bind="props" />
</template>

<script setup lang="ts">
import DiscoverPage, { type DiscoverPageProps } from './discover/index.vue';

const props = defineProps<DiscoverPageProps>();
</script>

