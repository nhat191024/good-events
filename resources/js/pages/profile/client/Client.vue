<script setup lang="ts">
import HeaderCard from './components/HeaderCard.vue'
import ContactCard from './components/ContactCard.vue'
import QuickStatsCard from './components/QuickStatsCard.vue'
import IntroCard from './components/IntroCard.vue'
import RecentOrdersCard from './components/RecentOrdersCard.vue'
import RecentReviewsCard from './components/RecentReviewsCard.vue'
import ReportModal from '@/components/ReportModal.vue'
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import { Head, Link, usePage } from '@inertiajs/vue3'
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import type { AppPageProps } from '@/types'
import { getImg } from '@/pages/booking/helper'
import ImageWithLoader from '@/components/ImageWithLoader.vue'
import { inject } from "vue";

const route = inject('route') as any;

interface UserInfo {
    id: number
    name: string
    avatar_url: string
    email: string
    phone: string
    created_year: string | null
    location: string | null
    partner_profile_name?: string | null
    bio?: string | null
    is_verified?: boolean
    email_verified_at?: string | null
}

interface BillItem {
    id: number
    code: string
    status: string
    final_total: number | null
    date: string | null
    event: string | null
    category: string | null
    partner_name: string | null
}

interface ReviewItem {
    id: number
    subject_name: string
    department: string
    review: string | null
    overall: number | null
    created_human: string | null
}

interface Props {
    user: UserInfo
    stats: {
        orders_placed: number
        completed_orders: number
        cancelled_orders_pct: string
        total_spent: number
        last_active_human: string | null
        avg_rating: number | null
    }
    recent_bills: BillItem[]
    recent_reviews: ReviewItem[]
    intro: string | null
}

const props = defineProps<Props>()

const page = usePage<AppPageProps>()
const authUser = computed(() => page.props.auth?.user ?? null)
const isOwnProfile = computed(() => authUser.value?.id === props.user.id)

const partnerDisplayName = computed(() => {
    if (props.user.partner_profile_name) {
        return `${props.user.name} (Partner name: ${props.user.partner_profile_name})`
    }
    return props.user.name
})

const isVerified = computed(() => Boolean(props.user.is_verified))

const introStats = computed(() => ({
    orders_placed: props.stats.orders_placed,
    avg_rating: props.stats.avg_rating ?? undefined,
}))

const menuOpen = ref(false)
const menuWrapper = ref<HTMLDivElement | null>(null)
const isReportModalOpen = ref(false)

const toggleMenu = () => {
    if (!isOwnProfile.value) {
        return
    }
    menuOpen.value = !menuOpen.value
}

const closeMenu = (event: MouseEvent) => {
    if (!menuWrapper.value) {
        return
    }

    if (!menuWrapper.value.contains(event.target as Node)) {
        menuOpen.value = false
    }
}

const handleEscape = (event: KeyboardEvent) => {
    if (event.key === 'Escape') {
        menuOpen.value = false
    }
}

onMounted(() => {
    document.addEventListener('click', closeMenu)
    document.addEventListener('keydown', handleEscape)
})

onBeforeUnmount(() => {
    document.removeEventListener('click', closeMenu)
    document.removeEventListener('keydown', handleEscape)
})
console.log('client page loaded');
</script>

<template>

    <Head :title="'Hồ sơ - ' + user.name" />
    <ClientHeaderLayout>
        <div class="w-full min-h-screen bg-gray-50">
            <!-- Banner with gradient and user header -->
            <div class="relative z-0 bg-gradient-to-br from-purple-400 via-rose-400 to-rose-500">
                <!-- Decorative overlay -->
                <div class="absolute inset-0 bg-black/10"></div>

                <!-- Banner content with user info -->
                <div class="relative container mx-auto px-4 py-4">
                    <div class="flex items-start gap-4">
                        <!-- Avatar -->
                        <div class="relative flex-shrink-0">
                            <div
                                class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white">
                                <ImageWithLoader v-if="user.avatar_url" :key="user.avatar_url"
                                    :src="getImg(user.avatar_url)" :alt="user.name" class="w-full h-full"
                                    img-class="w-full h-full object-cover" loading="lazy" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <!-- VIP badge -->
                            <!-- <div
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full border-2 border-white">
                                VIP
                            </div> -->
                        </div>

                        <!-- User info -->
                        <div class="flex-1 pt-2">
                            <div class="flex items-center gap-2 flex-wrap mb-2">
                                <h1 class="text-2xl md:text-3xl font-bold text-white">{{ partnerDisplayName }}</h1>
                                <span v-if="isVerified"
                                    class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs rounded-md border border-white/30">
                                    Đã xác minh
                                </span>
                            </div>

                            <div class="flex items-center gap-4 flex-wrap text-white/90 text-sm">
                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ user.location || 'Hà Nội' }}</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                                    </svg>
                                    <span>{{ stats.orders_placed.toLocaleString('vi-VN') }}+ đơn hàng</span>
                                </div>

                                <div class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Tham gia từ {{ user.created_year || '2025' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Menu button -->
                        <div v-if="isOwnProfile" class="relative flex-shrink-0" ref="menuWrapper">
                            <button type="button" aria-haspopup="menu" :aria-expanded="menuOpen"
                                @click.stop="toggleMenu"
                                class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white hover:bg-white/30 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
                                </svg>
                            </button>

                            <transition enter-active-class="transition ease-out duration-100"
                                enter-from-class="transform opacity-0 scale-95"
                                enter-to-class="transform opacity-100 scale-100"
                                leave-active-class="transition ease-in duration-75"
                                leave-from-class="transform opacity-100 scale-100"
                                leave-to-class="transform opacity-0 scale-95">
                                <div v-if="menuOpen"
                                    class="absolute right-0 mt-2 w-50 rounded-lg bg-white/95 shadow-lg ring-1 ring-black/10 backdrop-blur">
                                    <Link :href="route('profile.edit')"
                                        class="flex items-center gap-2 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 transition-colors rounded-lg"
                                        @click="menuOpen = false">
                                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5h2m-1-1v2m0 8v3m-3 0h6a2 2 0 002-2v-5a2 2 0 00-.59-1.41l-4-4a2 2 0 00-2.82 0l-4 4A2 2 0 006 10v5a2 2 0 002 2z" />
                                        </svg>
                                        Cài đặt hồ sơ
                                    </Link>
                                    <button type="button"
                                        class="w-full flex items-center gap-2 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 transition-colors rounded-lg"
                                        @click="() => { menuOpen = false; isReportModalOpen = true }">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                        </svg>
                                        Báo cáo người dùng
                                    </button>
                                </div>
                            </transition>
                        </div>

                        <!-- Report button for visitor -->
                        <div v-else class="relative flex-shrink-0">
                            <button type="button"
                                class="w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white hover:bg-white/30 transition-colors"
                                @click="isReportModalOpen = true" title="Báo cáo người dùng">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main content -->
            <div class="relative z-10 container mx-auto px-4 mt-6 pb-12">
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left sidebar -->
                    <div class="lg:col-span-4 xl:col-span-3 space-y-6">
                        <ContactCard :user="user" />
                        <QuickStatsCard :stats="stats" />
                    </div>

                    <!-- Main content area -->
                    <div class="lg:col-span-8 xl:col-span-9 space-y-6">
                        <IntroCard :intro="intro" :stats="introStats" />
                        <RecentOrdersCard :items="recent_bills" />
                        <RecentReviewsCard :items="recent_reviews" />
                    </div>
                </div>
            </div>
        </div>
    </ClientHeaderLayout>
    <ReportModal v-model:open="isReportModalOpen" :user-id="user.id" />
</template>
