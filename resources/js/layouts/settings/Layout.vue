<script setup lang="ts">
    import Heading from '@/components/Heading.vue';
    import { Button } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import { type NavItem } from '@/types';
    import { Link, usePage } from '@inertiajs/vue3';

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

    const currentPath = page.props.ziggy?.location ? new URL(page.props.ziggy.location).pathname : '';
</script>

<template>
    <div class="px-4 py-6 mt-14">
        <Heading title="Cài đặt" description="Quản lý hồ sơ và các thiết lập khác" />

        <div class="flex flex-col lg:flex-row lg:space-x-12">
            <aside class="w-full max-w-xl lg:w-48">
                <nav class="flex flex-col space-y-1 space-x-0">
                    <Button v-for="item in sidebarNavItems" :key="item.href" variant="ghost"
                        :class="['w-full justify-start', { 'bg-muted': currentPath === item.href }]" as-child>
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
