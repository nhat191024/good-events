<script setup lang="ts">
    import AppContent from '@/components/AppContent.vue';
    import AppHeader from '@/components/AppHeader.vue';
    import ConfirmModal from '@/components/ConfirmModal.vue'
    import Loading from '@/components/Loading.vue';
    import type { BreadcrumbItemType } from '@/types';

    interface Props {
        showBannerBackground?: boolean;
        bannerBackgroundClassName?: string;
        breadcrumbs?: BreadcrumbItemType[];
    }

    withDefaults(defineProps<Props>(), {
        showBannerBackground: () => true,
        breadcrumbs: () => [],
        bannerBackgroundClassName: () => 'bg-primary-200', // default color
    });

</script>

<template>
    <!-- <AppShell class="flex-col"> -->
        <!-- <AppHeader :breadcrumbs="breadcrumbs"/> -->
        <div class="pt-12" id="nav-bar-placeholder">
            <!-- this should be removed when the real navigation bar is merged -->
        </div>
        <AppContent>
            <!-- the red bg banner on top of the page -->
            <div v-if="showBannerBackground" :class="`absolute top-0 left-0 z-0 flex w-full h-48 items-center ${bannerBackgroundClassName || ''}`">
            </div>
            <slot />
        </AppContent>
        <div class="bg-white z-0 flex w-full h-68 items-center">
        </div>
    <!-- </AppShell> -->
        <ConfirmModal />
        <Loading />
</template>
