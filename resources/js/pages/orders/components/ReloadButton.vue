<script setup lang="ts">
withDefaults(defineProps<{
    isReloading?: boolean
}>(), {
    isReloading: false
});
const emit = defineEmits<{
    (e: 'reload'): void
}>();
</script>
<template>
    <!-- reload button -->
    <button type="button" @click="emit('reload')" :class="['h-8 w-8 text-2xl self-center justify-center rounded-full hover:bg-gray-300 active:bg-transparent transition cursor-pointer',
        { 'animate-spin': isReloading }]">
        ↻
    </button>
</template>
