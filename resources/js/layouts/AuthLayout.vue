<script setup lang="ts">
import TutorialHelpButton from '@/components/TutorialHelpButton.vue';
import AuthLayout from '@/layouts/auth/AuthSimpleLayout.vue';
import { clearDefaultTutorialLinks, setDefaultTutorialLinks } from '@/lib/tutorial-helper';
import { tutorialQuickLinks } from '@/lib/tutorial-links';
import { router } from '@inertiajs/core';
import { ArrowLeft } from 'lucide-vue-next';
import { inject } from "vue";

const route = inject('route') as any;

defineProps<{
    title?: string;
    description?: string;
}>();

function goHome(){
    router.get(route('home'))
}

setDefaultTutorialLinks([
    tutorialQuickLinks.seeAllTutorials,
    tutorialQuickLinks.clientRegister,
    tutorialQuickLinks.partnerLoginEvent,
    tutorialQuickLinks.partnerRegister,
]);
</script>

<template>
    <div class="mb-3 fixed left-3 top-1 pt-2">
        <button @click="goHome()"
            class="inline-flex items-center gap-2 text-sm px-3 h-9 rounded-xl border border-border bg-white/30 backdrop-blur-md shadow-sm">
            <ArrowLeft class="h-4 w-4" />
            Quay lại trang chủ
        </button>
    </div>
    <AuthLayout :title="title" :description="description">
        <slot />
    </AuthLayout>
    <TutorialHelpButton />
</template>
