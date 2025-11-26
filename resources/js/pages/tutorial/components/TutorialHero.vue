<template>
    <section class="relative overflow-hidden bg-gradient-to-b from-sky-50 via-white to-white py-3 sm:py-22">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center">
                <motion.div initial="{ opacity: 0, y: 20 }" animate="{ opacity: 1, y: 0 }"
                    transition="{ duration: 0.5 }">
                    <h1 class="text-4xl font-bold tracking-tight text-slate-900 sm:text-6xl">
                        {{ title }}
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-slate-600">
                        {{ description }}
                    </p>
                </motion.div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';

defineProps<{
    title: string;
    description: string;
}>();
</script>
