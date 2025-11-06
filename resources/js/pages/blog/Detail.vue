<template>
    <DetailPage v-bind="props" />
</template>

<script setup lang="ts">
import DetailPage, { type DetailPageProps } from './detail/index.vue';

const props = defineProps<DetailPageProps>();
</script>

