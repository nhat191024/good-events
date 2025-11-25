<script setup lang="ts">
    import { SidebarGroup, SidebarGroupLabel, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
    import { type NavItem } from '@/types';
    import { Link, usePage } from '@inertiajs/vue3';

    defineProps<{
        items: NavItem[];
    }>();

    const page = usePage();
    const activeStyle = {
        backgroundColor: '#ED3B50',
        color: '#ffffff',
        fontWeight: '600',
        boxShadow: '0 1px 2px 0 rgba(0,0,0,0.05), 0 0 0 1px rgb(220 38 38)',
    };

    const getPath = (url: string) => {
        try {
            return new URL(url).pathname;
        } catch (error) {
            return url;
        }
    }

    const isActive = (href: string) => getPath(href) === page.url.split('?')[0];
</script>

<template>
    <SidebarGroup class="px-2 py-0">
        <SidebarGroupLabel>Cài đặt</SidebarGroupLabel>
        <SidebarMenu>
            <SidebarMenuItem v-for="item in items" :key="item.title">
                <SidebarMenuButton as-child :is-active="isActive(item.href)" :tooltip="item.title">
                    <Link :href="item.href" :aria-current="isActive(item.href) ? 'page' : undefined"
                        :style="isActive(item.href) ? activeStyle : undefined">
                        <component :is="item.icon" />
                        <span>{{ item.title }}</span>
                    </Link>
                </SidebarMenuButton>
            </SidebarMenuItem>
        </SidebarMenu>
    </SidebarGroup>
</template>
