<template>
    <section class="flex flex-col gap-6">
        <p v-if="blog.excerpt" class="text-lg font-medium text-gray-700">
            {{ blog.excerpt }}
        </p>

        <div class="prose prose-lg max-w-none text-gray-700" v-html="blog.content" />
    </section>
</template>

<script setup lang="ts">
import type { BlogDetail } from '../../types';

defineProps<{ blog: BlogDetail }>();
</script>

