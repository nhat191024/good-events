<script setup lang="ts">
import { ref, watch } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import { motion } from 'motion-v';
import Icon from '@/components/Icon.vue';

// We don't declare specific props for src/alt to allow fallthrough,
// but we do need to handle the class prop specifically to apply it to the wrapper
// and ensure the image fills that wrapper.
const props = defineProps<{
    class?: string;
    imgClass?: string;
    src?: string;
    alt?: string;
    aspectRatio?: string; // e.g., "16/9", "4/3", "1/1"
}>();

const isLoading = ref(true);
const hasError = ref(false);

const onLoad = () => {
    isLoading.value = false;
};

const onError = () => {
    isLoading.value = false;
    hasError.value = true;
};

// Reset loading state if src changes
watch(() => props.src, () => {
    isLoading.value = true;
    hasError.value = false;
});
</script>

<template>
    <div :class="cn('relative overflow-hidden', props.class)" :style="[
        props.aspectRatio ? { aspectRatio: props.aspectRatio } : undefined,
        (hasError || isLoading) && !props.aspectRatio && (!props.class?.match(/\bh-(?!full\b)/)) ? { minHeight: '64px' } : undefined
    ]">
        <Skeleton v-if="isLoading" class="absolute inset-0 h-full w-full" />
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center z-10">
            <Icon name="Loader2" class="animate-spin text-gray-400" :size="48" />
        </div>
        <div v-if="hasError" class="absolute inset-0 flex items-center justify-center bg-primary-200/50 z-10">
            <Icon name="ImageOff" class="text-gray-400" :size="48" />
        </div>
        <motion.img v-if="!hasError" v-bind="$attrs" :src="props.src" :alt="props.alt" :class="cn(props.imgClass)"
            :initial="{ opacity: 0 }" :animate="{ opacity: isLoading ? 0 : 1 }" :transition="{ duration: 0.5 }"
            @load="onLoad" @error="onError" />
    </div>
</template>
