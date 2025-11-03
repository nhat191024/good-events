<template>
    <div class="container mx-auto px-4 py-4 md:py-6 space-y-12">
        <section class="mb-4" v-if="showSection">
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
        </section>
    </div>
</template>

<script setup lang="ts">
import { cn } from '@/lib/utils';
import { Link } from '@inertiajs/vue3';


interface Props {
    name: string;
    showSection: true
    href?: string
}

defineProps<Props>();

</script>
