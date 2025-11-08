<template>
    <div class="container mx-auto px-4 py-4 md:py-6 space-y-12">
        <motion.section
            v-if="showSection"
            :initial="sectionMotion.initial"
            :animate="sectionMotion.animate"
            class="mb-4">
            <!-- title -->
            <Link :href="href" :class="cn((href?'cursor-pointer':''))">
                <h2 :class="cn('text-xl font-bold text-gray-900 mb-6',(href?'cursor-pointer hover:underline':''))">
                    {{ name }}
                </h2>
            </Link>

            <!-- categories grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 md:gap-4 lg:gap-6 gap-2">
                <slot />
            </div>
        </motion.section>
    </div>
</template>

<script setup lang="ts">
import { motion } from 'motion-v';
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';

interface Props {
    name: string;
    showSection: true
    href?: string
}

defineProps<Props>();

const sectionMotion = {
    initial: {
        opacity: 0.5,
        y: 24,
    },
    animate: {
        opacity: 1,
        y: 0,
        transition: {
            delay: 0.3,
            duration: 0.6,
            ease: 'easeOut',
        },
    },
} as const;

</script>
