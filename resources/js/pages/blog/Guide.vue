<template>
    <GuidePage v-bind="props" />
</template>

<script setup lang="ts">
import GuidePage, { type GuidePageProps } from './guides/index.vue';

const props = defineProps<GuidePageProps>();
</script>
