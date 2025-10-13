<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import DropdownMenu from './DropdownMenu.vue';
import HamburgerMenu from './HamburgerMenu.vue';
import NotificationPopover from '@/components/notification/NotificationPopover.vue';
// import { NotiItem } from '@/components/notification';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useEcho, useEchoNotification } from '@laravel/echo-vue';

interface Props {
    // showBannerBackground?: boolean;
    // breadcrumbs?: BreadcrumbItemType[];
    backgroundClassNames?: string;
}

const page = usePage()
const user = page.props.auth?.user

withDefaults(defineProps<Props>(), {
    // showBannerBackground: () => true,
    // breadcrumbs: () => [],
    backgroundClassNames: () => 'bg-white', // default color
});

const menuItems = [
    { name: 'Sự kiện', slug: 'home', route: null },
    { name: 'Vật tư', slug: 'supply', route: null },
    { name: 'Tài liệu', slug: 'document', route: null },
    { name: 'Khách sạn', slug: 'blog', route: null }
];


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

function csrf(): string {
    return (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || ''
}

let aborter: AbortController | null = null
let pollTimer: number | null = null

async function loadNotifications(initial = false) {
    if (!userId.value) return // note: chưa login thì thôi
    if (isFetching.value) aborter?.abort()

    aborter = new AbortController()
    isFetching.value = true
    try {
        const res = await fetch(route('notifications.index'), {
            method: 'GET',
            headers: { Accept: 'application/json' },
            signal: aborter.signal,
            credentials: 'same-origin',
        })
        if (!res.ok) throw new Error(`http ${res.status}`)
        const json = await res.json()
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

async function markAsRead(item: NotiItem) {
    const id = item.id
    try {
        await fetch(route('notifications.read', { id }), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf(),
            },
            credentials: 'same-origin',
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
        await fetch(route('notifications.readAll'), {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
            credentials: 'same-origin',
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
        <div class="sm:px-4 lg:px-8 mx-auto">
            <div class="flex items-center justify-between h-16 md:px-3 px-1">
                <div class="flex items-center md:gap-3 gap-1">
                    <HamburgerMenu class="block md:hidden" :menu-items="menuItems" />
                    <!-- Logo + text -->
                    <Link :href="route('home')" class="flex items-center md:gap-2 gap-1">
                    <img src="/images/logo.png" alt="Sukientot"
                        class="h-9 w-9 rounded-full object-contain ring-2 ring-white/40" />
                    <span
                        class="font-bold tracking-tight text-black uppercase text-xs md:text-md lg:text-lg">SUKIENTOT.COM</span>
                    </Link>
                </div>

                <div class="hidden md:flex items-center gap-8">
                    <!-- Nav items (đậm, hover không đổi kích thước) -->
                    <nav class="flex items-center md:gap-3 lg:gap-6">
                        <Link :href="route('home')" class="font-semibold text-black hover:text-black/80">Sự Kiện</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Vật Tư</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Tài Liệu</Link>
                        <Link :href="'#'" class="font-semibold text-black hover:text-black/80">Khách sạn</Link>
                    </nav>
                </div>

                <!-- RIGHT: actions -->
                <div class="flex items-center md:gap-3 gap-1">
                    <!-- Pill: Đặt show nhanh -->
                    <Link :href="route('quick-booking.choose-category')"
                        class="inline-flex items-center md:gap-2 gap-1 rounded-full bg-[#ED3B50] px-4 sm:px-5 py-2 h-10 text-white font-semibold shadow-md shadow-[#ED3B50]/30 hover:bg-[#d93a4a] active:translate-y-[0.5px] whitespace-nowrap flex-shrink-0 transition">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                        <path
                            d="M7 3v3M17 3v3M3.5 9h17M7 13h4m-4 4h10M5 6h14a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2Z"
                            stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                        <path d="M15.5 10.5v3m-1.5-1.5h3" stroke="white" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="hidden sm:inline">Đặt show nhanh</span>
                    </Link>
                    <DropdownMenu />
                    <NotificationPopover :items="notificationItems" @mark-all-read="markAllAsRead" @select="markAsRead"/>
                </div>
            </div>
        </div>
    </header>
</template>
