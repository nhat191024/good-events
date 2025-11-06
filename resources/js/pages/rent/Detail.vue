<template>
    <RentDetailPage v-bind="props" />
</template>

<script setup lang="ts">
import RentDetailPage, { type DetailPageProps } from './detail/index.vue';

const props = defineProps<DetailPageProps>();
</script>
