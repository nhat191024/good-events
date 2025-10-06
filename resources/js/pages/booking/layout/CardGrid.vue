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
    <div :class="cn('rounded grid grid-cols-3 auto-rows-min h-min justify-content-center max-w-[1000px] overflow-visible p-1 md:p-[13px] relative w-full gap-x-[8px] gap-y-[8px] md:gap-x-[25px] md:gap-y-[20px] ', props.class)">
        <slot />
    </div>
</template>


