<script setup lang="ts">
import { RefreshCw } from 'lucide-vue-next';
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
    <button type="button" @click="emit('reload')"
        class="h-8 w-8 flex items-center justify-center rounded-full hover:bg-gray-100 active:bg-gray-200 transition-all cursor-pointer group"
        :class="{ 'pointer-events-none opacity-50': isReloading }">
        <RefreshCw
            :class="['h-4 w-4 text-gray-500 group-hover:text-primary-600 transition-colors', { 'animate-spin': isReloading }]" />
    </button>
</template>
