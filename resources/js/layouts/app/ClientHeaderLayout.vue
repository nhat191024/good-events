<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import ConfirmModal from '@/components/ConfirmModal.vue';
import Loading from '@/components/Loading.vue';
import TutorialHelpButton from '@/components/TutorialHelpButton.vue';
import Footer from '@/pages/home/partials/Footer.vue';
import Header from '@/pages/home/partials/Header.vue';
import Toast from '@/components/Toast.vue';
import SeoHead from '@/components/SeoHead.vue';
import { clearDefaultTutorialLinks, setDefaultTutorialLinks } from '@/lib/tutorial-helper';
import { tutorialQuickLinks } from '@/lib/tutorial-links';
import type { BreadcrumbItemType } from '@/types';
import { onMounted } from 'vue';

interface Props {
    showBannerBackground?: boolean;
    showFooter?: boolean;
    showNav?: boolean;
    backgroundClassNames?: string;
    breadcrumbs?: BreadcrumbItemType[];
}

setDefaultTutorialLinks([
    tutorialQuickLinks.seeAllTutorials,
]);

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
    <SeoHead />
    <Header v-if="showNav" :background-class-names="backgroundClassNames" />
    <main :class="showNav ? 'pt-16' : 'pt-0'">
        <div :class="['flex flex-col bg-white', showNav ? 'min-h-[calc(100vh-4rem)]' : 'min-h-screen']">
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
        <Toast />
        <TutorialHelpButton />
    </main>
</template>
