<script setup lang="ts">
    import type { HTMLAttributes } from 'vue'
    import { cn } from '@/lib/utils'
    import { type PrimitiveProps } from 'reka-ui'

    interface Props extends PrimitiveProps {
        class?: HTMLAttributes['class']
    }

    const props = withDefaults(defineProps<Props>(), {
        class: ''
    })
</script>

<template>
    <div :class="cn('h-min overflow-visible flex flex-col md:flex-row items-center gap-[10px] w-full p-0 relative ', props.class)">
        <slot />
    </div>
</template>


