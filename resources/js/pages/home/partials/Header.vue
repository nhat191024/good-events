<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import DropdownMenu from './DropdownMenu.vue';
import HamburgerMenu from './HamburgerMenu.vue';
import NotificationPopover from '@/components/notification/NotificationPopover.vue';
// import { NotiItem } from '@/components/notification';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useEcho, useEchoNotification } from '@laravel/echo-vue';
import axios from 'axios';
import { getImg } from '@/pages/booking/helper';
import { AppSettings } from '@/types';
import { motion } from 'motion-v';

interface Props {
    // showBannerBackground?: boolean;
    // breadcrumbs?: BreadcrumbItemType[];
    backgroundClassNames?: string;
}

const page = usePage()

const settings = computed(() => page.props.app_settings as AppSettings)
const user = page.props.auth?.user

withDefaults(defineProps<Props>(), {
    // showBannerBackground: () => true,
    // breadcrumbs: () => [],
    backgroundClassNames: () => 'bg-white', // default color
});

const menuItems = [
    { name: 'Nhân sự', shortName: 'Nhân sự', slug: 'home', routeName: 'home', route: route('home') },
    { name: 'Thiết bị sự kiện', shortName: 'Thiết bị', slug: 'supply', routeName: 'rent.home', route: route('rent.home') },
    { name: 'Thiết kế ', shortName: 'Tài liệu', slug: 'document', routeName: 'asset.home', route: route('asset.home') },
    { name: 'Địa điểm tổ chức', shortName: 'Địa điểm', slug: 'blog', routeName: 'blog.discover', route: route('blog.discover') },
    { name: 'Hướng dẫn tổ chức', shortName: 'Hướng dẫn', slug: 'guides', routeName: 'blog.guides.discover', route: route('blog.guides.discover') },
    { name: 'Kiến thức nghề', shortName: 'Kiến thức', slug: 'knowledge', routeName: 'blog.knowledge.discover', route: route('blog.knowledge.discover') },
    { name: 'Về chúng tôi', shortName: 'Giới thiệu', slug: 'about', routeName: 'about.index', route: route('about.index') },
];

const navLinkMotion = {
    hover: { translateY: -2, scale: 1.02 },
    tap: { scale: 0.98 },
    transition: { type: 'spring', stiffness: 260, damping: 22 },
} as const;


type NotiItem = {
    id: string
    title: string
    message: string
    unread: boolean
    created_at: string // iso8601
    payload?: Record<string, any>
}

const userId = computed<number | null>(() => (page.props as any)?.auth?.user?.id ?? null)
const notificationItems = ref<NotiItem[]>([])
const unreadCount = ref(0)
const isFloating = ref(false)
const isFetching = ref(false)

function mergeById(oldList: NotiItem[], incoming: NotiItem[]): NotiItem[] {
    // note: gộp theo id, ưu tiên bản mới từ server
    const map = new Map<string, NotiItem>(oldList.map(n => [n.id, n]))
    for (const n of incoming) map.set(n.id, { ...(map.get(n.id) ?? {}), ...n })
    return Array.from(map.values()).sort((a, b) => (a.created_at > b.created_at ? -1 : 1))
}

let aborter: AbortController | null = null
let pollTimer: number | null = null

async function loadNotifications(initial = false) {
    if (!userId.value) return // note: chưa login thì thôi
    if (isFetching.value) aborter?.abort()

    aborter = new AbortController()
    isFetching.value = true
    try {
        const res = await axios.get(route('notifications.index'), {
            headers: { Accept: 'application/json' },
            signal: aborter.signal,
        })
        const json = res.data
        const items: NotiItem[] = (json.data ?? []) as NotiItem[]
        notificationItems.value = mergeById(notificationItems.value, items)
        unreadCount.value = json?.meta?.unread_count ?? items.filter(i => i.unread).length

        if (initial) startPolling()
    } catch (e) {
    } finally {
        isFetching.value = false
    }
}

function startPolling() {
    if (pollTimer) window.clearInterval(pollTimer)
    pollTimer = window.setInterval(() => loadNotifications(false), 60_000) as unknown as number
}

function reloadNotifications() {
    loadNotifications(false)
}

async function markAsRead(item: NotiItem) {
    const id = item.id
    try {
        await axios.post(route('notifications.read', { id }), undefined, {
            headers: { Accept: 'application/json' },
        })
        const n = notificationItems.value.find(x => x.id === id)
        if (n && n.unread) {
            n.unread = false
            unreadCount.value = Math.max(0, unreadCount.value - 1)
        }
    } catch (e) {
    }
}

async function markAllAsRead() {
    try {
        await axios.post(route('notifications.readAll'), undefined, {
            headers: { Accept: 'application/json' },
        })
        notificationItems.value = notificationItems.value.map(n => ({ ...n, unread: false }))
        unreadCount.value = 0
    } catch (e) { }
}

function handleScroll() {
    isFloating.value = window.scrollY > 80
}

onMounted(() => {
    window.addEventListener('scroll', handleScroll)
    handleScroll()
    loadNotifications(true)
})

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll)
    if (pollTimer) window.clearInterval(pollTimer)
    aborter?.abort()
})

</script>

<template>
    <header :class="[
        backgroundClassNames || '',
        'fixed top-0 left-0 w-full z-50 transition-all duration-300',
        isFloating ? 'shadow-md bg-white/30 backdrop-blur-md' : backgroundClassNames + ' backdrop-blur-md'
    ]">
        <div class="md:px-1 lg:px-2 mx-auto">
            <div class="flex items-center justify-between h-16 px-0 px-1 gap-1">
                <div class="flex items-center gap-2">
                    <HamburgerMenu class="block lg:hidden" :menu-items="menuItems" />
                    <!-- Logo + text -->
                    <Link :href="route('home')" class="flex items-center md:gap-2 gap-1">
                    <img :src="getImg(`/${settings.app_logo}`)" alt="Sukientot"
                        class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40" />
                    <span
                        class="font-bold tracking-tight text-primary-700 uppercase text-xs md:text-md lg:text-lg">SUKIENTOT.COM</span>
                    </Link>
                </div>

                <div class="hidden lg:flex items-center gap-8">
                    <!-- Nav items (đậm, hover không đổi kích thước) -->
                    <nav class="flex items-center gap-3">
                        <motion.div v-for="item in menuItems" :key="item.slug" class="inline-flex"
                            :while-hover="navLinkMotion.hover" :while-tap="navLinkMotion.tap"
                            :transition="navLinkMotion.transition">
                            <Link :href="item.route" :class="[
                                'transition-colors duration-200',
                                route().current(item.routeName) ? 'text-[#ED3B50] text-md font-semibold ' : 'text-black hover:text-black/80'
                            ]">
                            {{ item.shortName }}
                            </Link>
                        </motion.div>
                    </nav>
                </div>

                <!-- RIGHT: actions -->
                <div class="flex items-center gap-1">
                    <!-- Pill: Đặt show nhanh -->
                    <Link :href="route('quick-booking.choose-category')"
                        class="self-start inline-flex items-center md:gap-2 gap-1 rounded-full bg-[#ED3B50] px-[10px] py-[20px] h-10 text-white font-semibold shadow-lg shadow-[#ED3B50]/30 hover:bg-[#d93a4a] active:translate-y-[0.5px] whitespace-nowrap flex-shrink-0 transition">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M7 3v3M17 3v3M3.5 9h17M7 13h4m-4 4h10M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                            stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.5 10.5v3m-1.5-1.5h3" stroke="white" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="hidden sm:inline">Đặt show</span>
                    </Link>
                    <DropdownMenu />
                    <NotificationPopover :items="notificationItems" :loading="isFetching" @mark-all-read="markAllAsRead"
                        @select="markAsRead" @reload="reloadNotifications" />
                </div>
            </div>
        </div>
    </header>
</template>
