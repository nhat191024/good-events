<script setup lang="ts">
    import Heading from '@/components/Heading.vue';
    import { Button } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import { type NavItem } from '@/types';
    import { Link, usePage } from '@inertiajs/vue3';
    import { computed } from 'vue';

    const sidebarNavItems: NavItem[] = [
        {
            title: 'Hồ sơ',
            href: route('profile.edit'),
        },
        {
            title: 'Mật khẩu',
            href: route('profile.password.edit'),
        },
        // {
        //     title: 'Giao diện',
        //     href: route('appearance'),
        // },
    ];

    const page = usePage();

    const normalizePath = (path: string): string => {
        const cleaned = path.replace(/\/+$/, '');
        return cleaned || '/';
    };

    const toPathname = (href: string): string => {
        try {
            const origin = page.props.ziggy?.location ?? (typeof window !== 'undefined' ? window.location.origin : 'http://localhost');
            return normalizePath(new URL(href, origin).pathname);
        } catch (error) {
            return normalizePath(href);
        }
    };

    const currentPath = computed(() => {
        if (!page.props.ziggy?.location) {
            return '';
        }

        try {
            return normalizePath(new URL(page.props.ziggy.location).pathname);
        } catch (error) {
            return normalizePath(page.props.ziggy.location);
        }
    });

    const isActive = (href: string): boolean => currentPath.value !== '' && toPathname(href) === currentPath.value;
</script>

<template>
    <div class="px-4 py-6 mt-8">
        <Heading title="Cài đặt" description="Quản lý hồ sơ và các thiết lập khác" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems" :key="item.href" variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': isActive(item.href) }]" as-child>
                        <Link :href="item.href">
                        {{ item.title }}
                        </Link>
                    </Button>
                </nav>
            </aside>

            <Separator class="my-6 lg:hidden" />

            <div class="flex-1 md:max-w-4xl">
                <section class="max-w-2xl space-y-12">
                    <slot />
                </section>
            </div>
        </div>
    </div>
</template>
