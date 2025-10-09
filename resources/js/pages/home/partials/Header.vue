<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import DropdownMenu from './DropdownMenu.vue';
import HamburgerMenu from './HamburgerMenu.vue';
import NotificationPopover from '@/components/notification/NotificationPopover.vue';
import { NotiItem } from '@/components/notification';
import { onMounted, onUnmounted, ref } from 'vue';

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

const notificationItems = ref<NotiItem[]>([
    { id: 1, title: 'Đơn hàng đã chốt', message: "đơn 'MC sự kiện' đã được chốt", unread: true,  created_at: 'vừa xong' },
    { id: 2, title: 'Ứng viên mới',     message: "có 1 ứng viên mới cho 'chủ hệ hoạt náo'", unread: false, created_at: '2 giờ trước' },
])

const isFloating = ref(false)


onMounted(() => {
    const handleScroll = () => {
        isFloating.value = window.scrollY > 80
    }
    window.addEventListener('scroll', handleScroll)
    handleScroll()
    onUnmounted(() => window.removeEventListener('scroll', handleScroll))
})

</script>

<template>
    <header :class="[
        backgroundClassNames || '',
        'fixed top-0 left-0 w-full z-50 transition-all duration-300',
        isFloating ? 'shadow-md bg-white/30 backdrop-blur-md' : backgroundClassNames+' backdrop-blur-md'
    ]">
        <div class="sm:px-4 lg:px-8 mx-auto">
            <div class="flex items-center justify-between h-16 pl-3 pr-3">
                <div class="flex items-center gap-3">
                    <!-- <HamburgerMenu :menu-items="menuItems" /> -->
                    <!-- Logo + text -->
                    <Link :href="route('home')" class="flex items-center gap-2">
                    <img src="/images/logo.png" alt="Sukientot"
                        class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40" />
                    <span
                        class="font-bold tracking-tight text-black uppercase text-sm md:text-md">SUKIENTOT.COM</span>
                    </Link>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <!-- Nav items (đậm, hover không đổi kích thước) -->
                    <nav class="flex items-center gap-6">
                        <Link :href="route('home')" class="font-semibold text-black hover:text-black/80">Sự Kiện</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Vật Tư</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Tài Liệu</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80 hidden md:block">Khách sạn</Link>
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
                    <NotificationPopover :items="notificationItems" />
                </div>
            </div>
        </div>
    </header>
</template>
