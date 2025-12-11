<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Loading from '@/components/Loading.vue';
import Footer from '@/pages/home/partials/Footer.vue';
import Header from '@/pages/home/partials/Header.vue';
import type { BreadcrumbItemType } from '@/types';
import { onMounted } from 'vue';

interface Props {
    showBannerBackground?: boolean;
    showFooter?: boolean;
    showNav?: boolean;
    backgroundClassNames?: string;
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    showBannerBackground: () => true,
    showFooter: () => true,
    showNav: () => true,
    breadcrumbs: () => [],
});

onMounted(() => {
    document.documentElement.classList.remove('dark');
});
</script>

<template>
    <Header v-if="showNav" :background-class-names="backgroundClassNames" />
    <main :class="showNav ? 'pt-16' : 'pt-0'">
        <div class="flex min-h-screen flex-col bg-white">
            <AppContent class="flex-1">
                <!-- the red bg banner on top of the page -->
                <!-- <div v-if="showBannerBackground" :class="`absolute top-0 left-0 z-0 flex w-full h-48 items-center ${bannerBackgroundClassName || ''}`"> -->
                <!-- </div> -->
                <slot />
            </AppContent>

            <Footer v-if="showFooter" />
        </div>

        <ConfirmModal />
        <Loading />
    </main>
</template>
