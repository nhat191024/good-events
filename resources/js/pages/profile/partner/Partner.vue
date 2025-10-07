<script setup lang="ts">
import PartnerContactCard from './components/PartnerContactCard.vue'
import PartnerQuickStatsCard from './components/PartnerQuickStatsCard.vue'
import PartnerServiceCard from './components/PartnerServiceCard.vue'
import PartnerIntroCard from './components/PartnerIntroCard.vue'
import PartnerReviewsCard from './components/PartnerReviewsCard.vue'
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import { Head } from '@inertiajs/vue3'

interface UserInfo {
    id: number; name: string; avatar_url: string; location: string | null;
    joined_year: string | null; is_pro: boolean; rating: number; total_reviews: number; total_customers: number | null;
}
interface Service { id: number; name: string; field: string; price: number | null }
interface Review { id: number; author: string; rating: number; review: string | null; created_human: string | null }

interface Props {
    user: UserInfo;
    stats: { customers: number; years: number; satisfaction_pct: string; avg_response: string };
    quick: { orders_placed: number; completed_orders: number; cancelled_orders_pct: string; last_active_human: string | null };
    contact: { phone: string | null; email: string | null; response_time: string; languages: string[]; timezone: string };
    services: Service[];
    reviews: Review[];
    intro: string | null;
}
const props = defineProps<Props>();
</script>

<template>
    <Head :title="'Hồ sơ đối tác - '+ user.name"/>
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
                                class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white">
                                <img v-if="user.avatar_url" :src="user.avatar_url" :alt="user.name"
                                    class="w-full h-full object-cover" />
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
                                class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full border-2 border-white">
                                PRO
                            </div>
                        </div>

                        <!-- User info -->
                        <div class="flex-1 text-white">
                            <div class="flex items-center gap-2 mb-1">
                                <h1 class="text-2xl md:text-3xl font-bold">{{ user.name }}</h1>
                                <span v-if="user.is_pro"
                                    class="px-2 py-0.5 bg-white/20 backdrop-blur-sm text-white text-xs rounded border border-white/30">
                                    Đã xác minh
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

                        <!-- Action buttons -->
                        <div class="flex items-center gap-2">
                            <button
                                class="p-2 bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-lg border border-white/30 text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
                                </svg>
                            </button>
                            <button
                                class="p-2 bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-lg border border-white/30 text-white transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                                </svg>
                            </button>
                            <button
                                class="p-2 bg-white/10 backdrop-blur-sm hover:bg-white/20 rounded-lg border border-white/30 text-white transition-colors">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z" />
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
                        <PartnerServiceCard :services="services" />
                        <PartnerReviewsCard :items="reviews" />
                    </div>
                </div>
            </div>
        </div>
    </ClientHeaderLayout>
</template>
