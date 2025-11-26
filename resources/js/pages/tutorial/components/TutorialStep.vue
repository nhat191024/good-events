<template>
    <div class="py-8 sm:py-12">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <motion.div
                class="mx-auto overflow-hidden rounded-3xl bg-white shadow-lg ring-1 ring-gray-900/5 lg:grid lg:max-w-none lg:grid-cols-2 lg:items-center"
                :initial="{ opacity: 0, y: 50 }" :while-in-view="{ opacity: 1, y: 0 }"
                :viewport="{ once: true, margin: '-100px' }" :transition="{ duration: 0.6 }">
                <div :class="['px-6 py-6 sm:px-12 lg:px-16', isReversed ? 'lg:order-last' : '']">
                    <div class="mx-auto max-w-lg lg:mx-0">
                        <div
                            class="inline-flex items-center rounded-full bg-sky-50 px-3 py-1 text-sm font-semibold text-sky-600 ring-1 ring-inset ring-sky-600/20">
                            Bước {{ stepNumber }}
                        </div>
                        <h2 class="mt-6 text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                            {{ title }}
                        </h2>
                        <p class="mt-6 text-lg leading-8 text-slate-600">
                            {{ description }}
                        </p>
                    </div>
                </div>
                <div
                    :class="['relative h-full min-h-[300px] w-full bg-gray-50 lg:min-h-[500px]', isReversed ? 'lg:order-first' : '']">
                    <img :src="image" :alt="title" class="absolute inset-0 h-full w-full object-cover" />
                    <!-- Gradient overlay for better text contrast if needed, though we separated text -->
                    <div class="absolute inset-0 ring-1 ring-inset ring-gray-900/5"></div>
                </div>
            </motion.div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';

defineProps<{
    stepNumber: number;
    title: string;
    description: string;
    image: string;
    isReversed?: boolean;
}>();
</script>
