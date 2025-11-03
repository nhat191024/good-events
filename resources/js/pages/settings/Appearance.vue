<script setup lang="ts">
    import { Head, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';

    import AppearanceTabs from '@/components/AppearanceTabs.vue';
    import HeadingSmall from '@/components/HeadingSmall.vue';
    import { type AppPageProps, type BreadcrumbItem } from '@/types';

    import AppLayout from '@/layouts/AppLayout.vue';
    import SettingsLayout from '@/layouts/settings/Layout.vue';

    interface AppearanceTranslations {
        breadcrumb: string;
        head_title: string;
        heading: {
            title: string;
            description: string;
        };
    }

    const page = usePage<AppPageProps<{ translations: AppearanceTranslations }>>();
    const translations = computed(() => page.props.translations as AppearanceTranslations);
    const breadcrumbItems = computed<BreadcrumbItem[]>(() => [
        {
            title: translations.value.breadcrumb,
            href: '/settings/appearance',
        },
    ]);
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbItems">

        <Head :title="translations.head_title" />

        <SettingsLayout>
            <div class="space-y-6">
                <HeadingSmall :title="translations.heading.title" :description="translations.heading.description" />
                <AppearanceTabs />
            </div>
        </SettingsLayout>
    </AppLayout>
</template>
