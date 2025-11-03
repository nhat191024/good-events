<script setup lang="ts">
    import type { HTMLAttributes } from 'vue'
    import { cn } from '@/lib/utils'
    import { useVModel } from '@vueuse/core'

    const props = defineProps<{
        defaultValue?: string | number
        modelValue?: string | number
        class?: HTMLAttributes['class']
        id?: string
    }>()

    const emits = defineEmits<{
        (e: 'update:modelValue', payload: string | number): void
    }>()

    const modelValue = useVModel(props, 'modelValue', emits, {
        passive: true,
        defaultValue: props.defaultValue,
    })
</script>

<template>
    <input :id="props.id" style="background: white;" v-model="modelValue" data-slot="input" :class="cn(
        'file:text-foreground placeholder:text-muted-foreground selection:bg-primary-700 selection:text-white dark:bg-input/30 border-gray-300 flex h-10 w-full min-w-0 rounded-md border px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none file:inline-flex file:h-7 file:border-0 file:bg-transparent file:text-sm file:font-medium disabled:pointer-events-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
        'focus-visible:border-primary-400 focus-visible:border-3',
        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
        props.class,
    )">
</template>
