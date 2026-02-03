<script setup lang="ts">
import PartnerContactCard from './components/PartnerContactCard.vue'
import PartnerQuickStatsCard from './components/PartnerQuickStatsCard.vue'
import PartnerServiceCard from './components/PartnerServiceCard.vue'
import PartnerIntroCard from './components/PartnerIntroCard.vue'
import PartnerReviewsCard from './components/PartnerReviewsCard.vue'
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import { Head, usePage, Link } from '@inertiajs/vue3'
import PartnerImagesCard from './components/PartnerImagesCard.vue'
import { getImg } from '@/pages/booking/helper'
import ImageWithLoader from '@/components/ImageWithLoader.vue'
import ReportModal from '@/components/ReportModal.vue'
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import type { AppPageProps } from '@/types'
import { inject } from "vue";
import PartnerVideoCard from './components/PartnerVideoCard.vue'

const route = inject('route') as any;

interface UserInfo {
    id: number; name: string; avatar_url: string; location: string | null;
    joined_year: string | null; is_pro: boolean; rating: number; total_reviews: number; total_customers: number | null;
    bio?: string | null; is_verified?: boolean; email_verified_at?: string | null; is_legit?: boolean;
}
type Media = { id: number; url: string; image_tag?: string }
type Service = { id: number; name: string | null; field: string | null; price: number | null; media: Media[] }
interface Review { id: number; author: string; rating: number; review: string | null; created_human: string | null }

interface Props {
    user: UserInfo;
    stats: { customers: number; years: number; satisfaction_pct: string; avg_response: string };
    quick: { orders_placed: number; completed_orders: number; cancelled_orders_pct: string; last_active_human: string | null };
    contact: { phone: string | null; email: string | null; response_time: string; languages: string[]; timezone: string };
    services: Service[];
    reviews: Review[];
    intro: string | null;
    video_url: string | null;
}
const props = defineProps<Props>();
const page = usePage<AppPageProps>()
const isReportModalOpen = ref(false)
const isOwnProfile = computed(() => {
    return page.props.auth?.user?.id === props.user.id
})

const menuOpen = ref(false)
const menuWrapper = ref<HTMLDivElement | null>(null)

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

console.log('partner page loaded');

</script>

<template>

    <Head :title="'Hồ sơ đối tác - ' + user.name" />
    <ClientHeaderLayout>
        <div class="min-h-screen w-full bg-gray-50">
            <!-- Banner (match client profile spacing) -->
            <div class="relative z-0 bg-gradient-to-br from-purple-400 via-rose-400 to-rose-500">
                <!-- Optional background image overlay -->
                <div class="absolute inset-0 bg-[url('/placeholder.jpg')] bg-cover bg-center opacity-20"></div>

                <!-- Header content integrated into banner -->
                <div class="relative container mx-auto px-4 py-4">
                    <div class="flex items-start gap-4">
                        <!-- Avatar with badge -->
                        <div class="relative">
                            <div
                                class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white overflow-hidden bg-white"
                                :class="user.is_legit ? 'ring-4 ring-amber-300/80 shadow-[0_0_20px_rgba(251,191,36,0.7)]' : 'shadow-lg'">
                                <ImageWithLoader v-if="user.avatar_url" :src="getImg(user.avatar_url)" :alt="user.name" :img-tag="user.avatar_img_tag"
                                    class="w-full h-full" img-class="w-full h-full object-cover" loading="lazy" />
                                <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
                                    <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-400" fill="currentColor"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </div>
                            <div
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full border-2 border-white z-10">
                                PARTNER
                            </div>
                        </div>

                        <!-- User info -->
                        <div class="flex-1 text-white">
                            <div class="flex items-center gap-2 mb-1">
                                <h1 class="text-2xl md:text-3xl font-bold">{{ user.name }}</h1>
                                <span v-if="user.is_verified"
                                    class="px-2 py-0.5 bg-white/20 backdrop-blur-sm text-white text-xs rounded border border-white/30"
                                    title="Người dùng này đã xác minh email" aria-label="Người dùng này đã xác minh email">
                                    Đã xác minh
                                </span>
                                <span v-if="user.is_legit"
                                    class="px-2 py-0.5 bg-amber-300/30 backdrop-blur-sm text-amber-50 text-xs rounded border border-amber-200/60"
                                    title="Người dùng đã xác minh thông tin cá nhân chuẩn thông qua KYC"
                                    aria-label="Người dùng đã xác minh thông tin cá nhân chuẩn thông qua KYC">
                                    Đáng tin cậy
                                </span>
                            </div>

                            <!-- Rating -->
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex items-center gap-1 text-yellow-400">
                                    <template v-for="n in 5" :key="n">
                                        <svg class="w-4 h-4 fill-current" viewBox="0 0 20 20">
                                            <path
                                                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                    </template>
                                </div>
                                <span class="text-white font-semibold">{{ user.rating.toFixed(1) }}</span>
                                <span class="text-white/80 text-sm">({{ user.total_reviews }} đánh giá)</span>
                            </div>

                            <!-- Location and stats -->
                            <div class="flex items-center gap-4 text-sm text-white/90">
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span>{{ user.location || '—' }}</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                    </svg>
                                    <span>{{ user.total_customers || '—' }}+ khách hàng</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <span>Tham gia từ {{ user.joined_year || '—' }}</span>
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

            <!-- Content section with proper spacing -->
            <div class="container mx-auto px-4 py-6">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6">
                    <!-- Left Sidebar -->
                    <div class="md:col-span-3 space-y-4">
                        <PartnerContactCard :contact="contact" />
                        <PartnerQuickStatsCard :quick="quick" />
                    </div>

                    <!-- Right Main Content -->
                    <div class="md:col-span-9 space-y-4">
                        <PartnerIntroCard :intro="intro" :stats="stats" />
                        <PartnerVideoCard :iframe="video_url" />
                        <PartnerServiceCard :services="services" />
                        <PartnerReviewsCard :items="reviews" />
                    </div>

                    <div class="md:col-span-12 space-y-4">
                        <PartnerImagesCard :services="services" />
                    </div>
                </div>
            </div>
        </div>
    </ClientHeaderLayout>
    <ReportModal v-model:open="isReportModalOpen" :user-id="user.id" />
</template>
