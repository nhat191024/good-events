<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, computed, watch, type Ref, type Component } from 'vue';
import { User, ClipboardList, MessageSquare, Settings, HelpCircle, Handshake, Shield, LogOut, FileCheck, PhoneCall, Info } from 'lucide-vue-next';

import DropdownMenuItems from '../components/DropdownMenuItems.vue';

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

type MenuItem = {
    label: string;
    route: () => string;
    icon: Component;
    external?: boolean;
    hoverClass?: string;
    textClass?: string;
};

const isOpen: Ref<boolean> = ref(false);
const dropdown: Ref<HTMLDivElement | null> = ref(null);
const avatarStatus = ref<'idle' | 'loaded' | 'error'>(user.value?.avatar_url ? 'idle' : 'error');

const avatarUrl = computed<string | null>(() => user.value?.avatar_url ?? null);
const shouldRenderImage = computed(() => !!avatarUrl.value && avatarStatus.value !== 'error');
const showFallbackIcon = computed(() => !avatarUrl.value || avatarStatus.value !== 'loaded');

const toggleDropdown = (): void => {
    isOpen.value = !isOpen.value;
};

const handleClickOutside = (event: MouseEvent): void => {
    if (dropdown.value && !dropdown.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

const handleAvatarLoad = () => {
    avatarStatus.value = 'loaded';
};

const handleAvatarError = () => {
    avatarStatus.value = 'error';
};

watch(
    () => avatarUrl.value,
    (newUrl) => {
        avatarStatus.value = newUrl ? 'idle' : 'error';
    },
    { immediate: true },
);

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

const isLoggedIn = () => {
    return !!page.props.auth?.user;
};

const handleLogout = () => {
    router.flushAll();
};

// Navigation items for logged-in users
const loggedInMenuItems = computed<MenuItem[]>(() => [
    {
        label: 'Hồ sơ cá nhân',
        route: () => route('profile.client.show', { user: user.value?.id ?? 1 }),
        icon: User,
        external: false
    },
    {
        label: 'Đơn hàng của tôi',
        route: () => route('client-orders.dashboard'),
        icon: ClipboardList,
        external: false
    },
    {
        label: 'File đã mua',
        route: () => route('client-orders.asset.dashboard'),
        icon: FileCheck,
        external: false
    },
    {
        label: 'Nhắn tin',
        route: () => route('chat.index'),
        icon: MessageSquare,
        external: false
    },
    {
        label: 'Cài đặt',
        route: () => route('profile.edit'),
        icon: Settings,
        external: false
    },
    {
        label: 'Liên hệ',
        route: () => route('contact.index'),
        icon: PhoneCall,
        external: false
    },
    {
        label: 'Về chúng tôi',
        route: () => route('about.index'),
        icon: Info,
        external: false
    },
    {
        label: 'Trang đối tác',
        route: () => '/partner/login',
        icon: Handshake,
        external: true,
        hoverClass: 'hover:bg-gray-100'
    },
    {
        label: 'Trang quản trị',
        route: () => '/admin/login',
        icon: Shield,
        external: true,
        hoverClass: 'hover:bg-gray-100' // use a regular <a> tag, non inertia
    },
]);

// Navigation items for logged-out users
const loggedOutMenuItems: MenuItem[] = [
    {
        label: 'Đăng nhập/Đăng ký',
        route: () => route('login'),
        icon: User,
        external: false
    },
    {
        label: 'Khám phá nhân sự',
        route: () => route('home'),
        icon: HelpCircle,
        external: false
    },
    {
        label: 'Về chúng tôi',
        route: () => route('about.index'),
        icon: Info,
        external: false
    },
    {
        label: 'Liên hệ',
        route: () => route('contact.index'),
        icon: PhoneCall,
        external: false
    },
    {
        label: 'Trang đối tác',
        route: () => '/partner/login',
        icon: Handshake,
        external: true
    },
    {
        label: 'Trang quản trị',
        route: () => '/admin/login',
        icon: Shield,
        external: true
    },
];
</script>

<template>
    <div class="relative" ref="dropdown">
        <button type="button" aria-label="Tài khoản" @click="toggleDropdown"
            class="relative cursor-pointer h-10 w-10 rounded-full bg-[#ED3B50] text-white shadow-lg shadow-[#ED3B50]/30 transition focus:outline-none focus-visible:ring-2 border border-primary-200 focus-visible:ring-offset-2 focus-visible:ring-[#ED3B50] overflow-show">
            <img v-if="shouldRenderImage" :src="avatarUrl ?? ''" alt="Avatar"
                class="absolute rounded-full inset-0 h-full w-full object-cover transition-opacity duration-200 z-0"
                :class="avatarStatus === 'loaded' ? 'opacity-100' : 'opacity-0'" @load="handleAvatarLoad"
                @error="handleAvatarError">
            <div v-if="showFallbackIcon" class="grid h-full w-full place-items-center">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                    <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"
                        fill="currentColor" />
                </svg>
            </div>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                class="absolute -bottom-1 -right-1 bg-[#ED3B50] rounded-full z-10" :class="{ 'rotate-180': isOpen }">
                <path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                    stroke-linejoin="round" />
            </svg>
        </button>

        <transition enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95" enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75" leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95">
            <div v-if="isOpen"
                class="absolute right-0 mt-2 w-56 rounded-lg shadow-lg bg-white ring-1 ring-primary-200 ring-opacity-5 z-50">
                <div v-if="isLoggedIn()" class="py-0">
                    <DropdownMenuItems :items="loggedInMenuItems" hover-class="hover:bg-red-100" />
                    <hr class="my-1 border-gray-200">

                    <Link method="post" :href="route('logout')" @click="handleLogout" as="button"
                        class="first:pt-3 last:pb-3 first:rounded-t-lg flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <LogOut class="w-5 h-5 mr-3" />
                    Đăng xuất
                    </Link>
                </div>

                <div v-else class="py-0">
                    <DropdownMenuItems :items="loggedOutMenuItems" hover-class="hover:bg-gray-100" />
                </div>
            </div>
        </transition>
    </div>
</template>
