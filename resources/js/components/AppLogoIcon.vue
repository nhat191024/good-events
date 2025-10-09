<script setup lang="ts">
    import type { HTMLAttributes } from 'vue';

    defineOptions({
        inheritAttrs: false,
    });

    interface Props {
        className?: HTMLAttributes['class'];
    }

    defineProps<Props>();
</script>

<template>
    <img src="/images/logo.png" alt="Sukientot" class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40" />
</template>
