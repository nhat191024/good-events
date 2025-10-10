<script setup lang="ts">
import { Link, router, usePage } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount, computed, type Ref } from 'vue';

const page = usePage()
const user = page.props.auth?.user

const isOpen: Ref<boolean> = ref(false);
const dropdown: Ref<HTMLDivElement | null> = ref(null);

const toggleDropdown = (): void => {
    isOpen.value = !isOpen.value;
};

const handleClickOutside = (event: MouseEvent): void => {
    if (dropdown.value && !dropdown.value.contains(event.target as Node)) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
});

const isLoggedIn = () => {
    return (!!page.props.auth?.user)
}

const handleLogout = () => {
    router.flushAll();
};

// Navigation items for logged-in users
const loggedInMenuItems = computed(() => [
    {
        label: 'Hồ sơ cá nhân',
        route: () => route('profile.client.show', { user: user?.id ?? 1 }),
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        external: false
    },
    {
        label: 'Đơn hàng của tôi',
        route: () => route('client-orders.dashboard'),
        icon: 'M5 8h14l-1.5 12.5A2 2 0 0115.5 23h-7a2 2 0 01-1.99-2.5L5 8zm3 0V6a4 4 0 018 0v2',
        external: false
    },
    {
        label: 'Nhắn tin',
        route: () => route('chat.dashboard'),
        icon: 'M8 10h8m-8 4h5m1 8a9 9 0 100-18 9 9 0 00-9 9c0 1.657.403 3.214 1.122 4.583L3 21l4.417-1.122A8.963 8.963 0 0012 22z',
        external: false
    },
    {
        label: 'Cài đặt',
        route: () => route('home'),
        icon: 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        external: false
    },
    {
        label: 'Trợ giúp',
        route: () => route('home'),
        icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        external: false
    },
    {
        label: 'Trang đối tác',
        route: () => '/partner/login',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        external: true
    },
    {
        label: 'Trang quản trị',
        route: () => '/admin/login',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        external: true  // use a regular <a> tag, non inertia
    },
]);

// Navigation items for logged-out users
const loggedOutMenuItems = [
    {
        label: 'Đăng nhập/Đăng ký',
        route: () => route('login'),
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        external: false
    },
    {
        label: 'Trợ giúp',
        route: () => route('home'),
        icon: 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        external: false
    },
    {
        label: 'Trang đối tác',
        route: () => '/partner/login',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        external: true
    },
    {
        label: 'Trang quản trị',
        route: () => '/admin/login',
        icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        external: true
    },
];
</script>

<template>
    <div class="relative" ref="dropdown">
        <button type="button" aria-label="Tài khoản" @click="toggleDropdown"
            class="cursor-pointer h-10 w-10 rounded-full bg-[#ED3B50] text-white grid place-items-center shadow-lg shadow-[#ED3B50]/30 hover:bg-[#d93a4a] transition">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none">
                <path d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"
                    fill="currentColor" />
            </svg>
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none"
                class="absolute -bottom-1 -right-1 bg-[#ED3B50] rounded-full" :class="{ 'rotate-180': isOpen }">
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
                <div v-if="isLoggedIn()" class="py-1">
                    <template v-for="(item, index) in loggedInMenuItems" :key="index">
                        <Link v-if="!item.external" :href="item.route()"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                        </Link>

                        <a v-else :href="item.route()"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            {{ item.label }}
                        </a>
                    </template>

                    <hr class="my-1 border-gray-200">

                    <Link method="post" :href="route('logout')" @click="handleLogout" as="button"
                        class="flex w-full items-center px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition">
                    <svg class="w-5 h-5 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Đăng xuất
                    </Link>
                </div>

                <div v-else class="py-1">
                    <template v-for="(item, index) in loggedOutMenuItems" :key="index">
                        <Link v-if="!item.external" :href="item.route()"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                        <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                        </svg>
                        {{ item.label }}
                        </Link>

                        <a v-else :href="item.route()"
                            class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 transition">
                            <svg class="w-5 h-5 mr-3 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="item.icon" />
                            </svg>
                            {{ item.label }}
                        </a>
                    </template>
                </div>
            </div>
        </transition>
    </div>
</template>
