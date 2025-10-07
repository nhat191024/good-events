<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import DropdownMenu from './DropdownMenu.vue';
import HamburgerMenu from './HamburgerMenu.vue';

interface Props {
    // showBannerBackground?: boolean;
    backgroundClassNames?: string;
    // breadcrumbs?: BreadcrumbItemType[];
}

const page = usePage()
const user = page.props.auth?.user

withDefaults(defineProps<Props>(), {
    // showBannerBackground: () => true,
    // breadcrumbs: () => [],
    backgroundClassNames: () => 'bg-white', // default color
});

const menuItems = [
    { name: 'Chú hề', slug: 'home', route: null },
    { name: 'MC', slug: 'about', route: null },
    { name: 'Liên hệ', slug: 'contact', route: null }
];

</script>

<template>
    <header :class="`${backgroundClassNames || ''}`">
        <div class="sm:px-6 lg:px-8 mx-auto max-w-7xl">
            <div class="flex items-center justify-between h-16">
                <!-- LEFT: hamburger + logo -->
                <div class="flex items-center gap-3">
                    <!-- Hamburger (tròn trắng, đổ bóng nhẹ) -->
                    <HamburgerMenu :menu-items="menuItems" />

                    <!-- Logo + text -->
                    <Link :href="route('home')" class="flex items-center gap-2">
                    <img src="/images/logo.png" alt="Sukientot"
                        class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40" />
                    <span
                        class="text-base sm:text-lg font-bold tracking-tight text-black uppercase">SUKIENTOT.COM</span>
                    </Link>
                </div>

                <div>
                    <!-- Dành cho nhân sự (icon + text đậm) -->
                    <a :href="route('filament.partner.pages.dashboard')"
                        class="hidden lg:flex items-center gap-2 text-black font-semibold">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" class="opacity-90">
                            <path
                                d="M12 12a4 4 0 1 0-4-4 4 4 0 0 0 4 4Zm0 2c-4.418 0-8 2.239-8 5v1h16v-1c0-2.761-3.582-5-8-5Z"
                                fill="currentColor" />
                        </svg>
                        <span class="font-lexend">Trang Đối tác</span>
                    </a>
                </div>

                <!-- CENTER: “Dành cho nhân sự” + nav -->
                <div class="hidden md:flex items-center gap-8">


                    <!-- Nav items (đậm, hover không đổi kích thước) -->
                    <nav class="flex items-center gap-6">
                        <Link :href="route('home')" class="font-semibold text-black hover:text-black/80">Sự Kiện</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Vật Tư</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Tài Liệu</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Khách sạn</Link>
                    </nav>
                </div>

                <!-- RIGHT: actions -->
                <div class="flex items-center gap-3">
                    <!-- Pill: Đặt show nhanh -->
                    <Link :href="route('quick-booking.choose-category')"
                        class="inline-flex items-center gap-2 rounded-full bg-[#ED3B50] px-4 sm:px-5 py-2 h-10 text-white font-semibold shadow-md shadow-[#ED3B50]/30 hover:bg-[#d93a4a] active:translate-y-[0.5px] whitespace-nowrap flex-shrink-0 transition">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M7 3v3M17 3v3M3.5 9h17M7 13h4m-4 4h10M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                            stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.5 10.5v3m-1.5-1.5h3" stroke="white" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="hidden sm:inline">Đặt show nhanh</span>
                    </Link>

                    <DropdownMenu />
                </div>
            </div>
        </div>
    </header>
</template>
