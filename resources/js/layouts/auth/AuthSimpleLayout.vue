<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import Toast from '@/components/Toast.vue';
import { Link } from '@inertiajs/vue3';

defineProps<{
    title?: string;
    description?: string;
}>();
</script>

<template>
    <div class="w-full flex min-h-svh flex-col items-center justify-start gap-6 bg-background p-6 md:p-10 pt-14">
        <div class="w-full max-w-sm">
            <div class="flex flex-col gap-6">
                <div class="flex flex-row items-center w-full gap-4 justify-center mt-3">
                    <Link :href="route('home')" class="flex flex-col items-center gap-2 font-medium">
                        <div class="mb-1 flex h-9 w-9 items-center justify-center rounded-md">
                            <AppLogoIcon class="size-9 fill-current text-[var(--foreground)] dark:text-white" />
                        </div>
                        <span class="sr-only">{{ title }}</span>
                    </Link>
                    <div class="space-y-2 text-left">
                        <h1 class="text-xl font-medium">{{ title }}</h1>
                        <p class="text-left text-sm text-muted-foreground">{{ description }}</p>
                    </div>
                </div>
                <slot />
            </div>
        </div>
    </div>
    <Toast />
</template>
