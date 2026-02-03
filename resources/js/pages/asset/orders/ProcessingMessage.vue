<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Props {
    type?: 'info' | 'success' | 'warning' | 'error';
    title: string;
    message: string;
    backUrl: string;
    backText: string;
    processing?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    type: 'info',
    processing: false,
});

const iconClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'text-green-600 dark:text-green-400';
        case 'warning':
            return 'text-yellow-600 dark:text-yellow-400';
        case 'error':
            return 'text-red-600 dark:text-red-400';
        default:
            return 'text-blue-600 dark:text-blue-400';
    }
});

const bgClass = computed(() => {
    switch (props.type) {
        case 'success':
            return 'bg-green-50 dark:bg-green-950/20';
        case 'warning':
            return 'bg-yellow-50 dark:bg-yellow-950/20';
        case 'error':
            return 'bg-red-50 dark:bg-red-950/20';
        default:
            return 'bg-blue-50 dark:bg-blue-950/20';
    }
});

const icon = computed(() => {
    switch (props.type) {
        case 'success':
            return `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
        case 'warning':
            return `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>`;
        case 'error':
            return `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
        default:
            return `<svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>`;
    }
});
</script>

<template>
    <Head :title="title" />

    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4 dark:bg-gray-900">
        <div class="w-full max-w-md">
            <div :class="[bgClass, 'rounded-lg p-8 text-center shadow-lg']">
                <!-- Icon -->
                <div :class="iconClass" class="mb-4 flex justify-center">
                    <div v-html="icon"></div>
                </div>

                <!-- Title -->
                <h1 class="mb-4 text-2xl font-bold text-gray-900 dark:text-gray-100">
                    {{ title }}
                </h1>

                <!-- Message -->
                <p class="mb-6 text-gray-700 dark:text-gray-300">
                    {{ message }}
                </p>

                <!-- Processing Spinner -->
                <div v-if="processing" class="mb-6 flex justify-center">
                    <svg
                        class="h-8 w-8 animate-spin text-blue-600 dark:text-blue-400"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>
                </div>

                <!-- Back Button -->
                <Link
                    :href="backUrl"
                    class="inline-flex items-center justify-center rounded-md border border-transparent bg-blue-600 px-6 py-3 text-base font-medium text-white transition-colors duration-200 hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600"
                >
                    {{ backText }}
                </Link>
            </div>
        </div>
    </div>
</template>
