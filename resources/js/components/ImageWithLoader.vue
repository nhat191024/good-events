<script setup lang="ts">
import { computed, ref, watch, useAttrs } from 'vue';
import { Skeleton } from '@/components/ui/skeleton';
import { cn } from '@/lib/utils';
import { motion } from 'motion-v';
import { Loader2, ImageOff } from 'lucide-vue-next';

// We don't declare specific props for src/alt to allow fallthrough,
// but we do need to handle the class prop specifically to apply it to the wrapper
// and ensure the image fills that wrapper.
const props = defineProps<{
    class?: string;
    imgClass?: string;
    src?: string;
    alt?: string;
    aspectRatio?: string; // e.g., "16/9", "4/3", "1/1"
    imgTag?: string;
}>();

const attrs = useAttrs();

const isLoading = ref(true);
const hasError = ref(false);

const onLoad = () => {
    isLoading.value = false;
};

const onError = () => {
    isLoading.value = false;
    hasError.value = true;
};

const parsedImg = computed(() => {
    if (!props.imgTag) return null;
    try {
        const doc = new DOMParser().parseFromString(props.imgTag, 'text/html');
        const img = doc.querySelector('img');
        if (!img) return null;

        const attributes: Record<string, string> = {};
        Array.from(img.attributes).forEach((attr) => {
            attributes[attr.name] = attr.value;
        });

        return {
            src: attributes.src,
            alt: attributes.alt,
            className: attributes.class,
            attributes,
        };
    } catch (error) {
        console.warn('Failed to parse imgTag', error);
        return null;
    }
});

const parsedAttrs = computed(() => {
    const base = parsedImg.value?.attributes ?? {};
    // Remove props we already handle explicitly
    // eslint-disable-next-line @typescript-eslint/no-unused-vars
    const { src, alt, class: className, ...rest } = base as Record<string, string>;
    return rest;
});

const mergedAttrs = computed(() => ({
    ...parsedAttrs.value,
    ...attrs,
}));

const effectiveSrc = computed(() => parsedImg.value?.src ?? props.src);
const effectiveAlt = computed(() => props.alt ?? parsedImg.value?.alt ?? undefined);
const mergedImgClass = computed(() => cn(parsedImg.value?.className, props.imgClass));

// Reset loading state if src or imgTag changes
watch(
    () => [props.src, props.imgTag],
    () => {
        isLoading.value = true;
        hasError.value = false;
    }
);
</script>

<template>
    <div :class="cn('relative overflow-hidden', props.class)" :style="[
        props.aspectRatio ? { aspectRatio: props.aspectRatio } : undefined,
        (hasError || isLoading) && !props.aspectRatio && (!props.class?.match(/\bh-(?!full\b)/)) ? { minHeight: '64px' } : undefined
    ]">
        <Skeleton v-if="isLoading" class="absolute inset-0 h-full w-full" />
        <div v-if="isLoading" class="absolute inset-0 flex items-center justify-center z-10">
            <Loader2 class="animate-spin text-gray-400" :size="48" />
        </div>
        <div v-if="hasError" class="absolute inset-0 flex items-center justify-center bg-primary-200/50 z-10">
            <ImageOff class="text-gray-400" :size="48" />
        </div>
        <motion.img v-if="!hasError" v-bind="mergedAttrs" :src="effectiveSrc" :alt="effectiveAlt" :class="mergedImgClass"
            :initial="{ opacity: 0 }" :animate="{ opacity: isLoading ? 0 : 1 }" :transition="{ duration: 0.5 }"
            @load="onLoad" @error="onError" />
    </div>
</template>
