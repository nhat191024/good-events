<template>
    <KnowledgePage v-bind="props" />
</template>

<script setup lang="ts">
import KnowledgePage, { type KnowledgePageProps } from './knowledge/index.vue';

const props = defineProps<KnowledgePageProps>();
</script>
