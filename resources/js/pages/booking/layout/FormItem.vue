<script setup lang="ts">
import { cn } from '@/lib/utils';

    interface Props {
        forId?: string;
        label: string;
        error?: string;
        class?: string;
    }

    const props = withDefaults(defineProps<Props>(),{
        forId: '',
        label: 'Label here'
    });
</script>

<template>
    <label :for="forId" :class="cn('flex-col flex grow place-content-start items-start gap-1 h-min w-full p-0 relative', props.class)">
        <div class="outline-0 flex flex-col justify-start transform-none relative whitespace-pre w-auto h-auto">
            <p v-text="label" class="text-sm font-semibold text-black"></p>
        </div>
        <div class="outline-0 flex flex-col justify-start transform-none relative whitespace-pre w-full h-auto">
            <slot/>
        </div>
        <div v-if="error" class="mt-1 text-xs text-red-500">
            <!-- <slot name="error" /> -->
            <span class="text-red-700" v-text="error"></span>
        </div>
    </label>
</template>
