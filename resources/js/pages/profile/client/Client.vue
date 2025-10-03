<script setup lang="ts">
import HeaderCard from './components/HeaderCard.vue'
import ContactCard from './components/ContactCard.vue'
import QuickStatsCard from './components/QuickStatsCard.vue'
import IntroCard from './components/IntroCard.vue'
import RecentOrdersCard from './components/RecentOrdersCard.vue'
import RecentReviewsCard from './components/RecentReviewsCard.vue'

interface UserInfo {
  id: number
  name: string
  avatar_url: string
  email: string
  phone: string
  created_year: string | null
  location: string | null
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
  }
  recent_bills: BillItem[]
  recent_reviews: ReviewItem[]
  intro: string | null
}

const props = defineProps<Props>()
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Banner with gradient and user header -->
    <div class="relative z-0 bg-gradient-to-br from-purple-400 via-rose-400 to-rose-500">
      <!-- Decorative overlay -->
      <div class="absolute inset-0 bg-black/10"></div>

      <!-- Banner content with user info -->
      <div class="relative container mx-auto px-4 py-4">
        <div class="flex items-start gap-4">
          <!-- Avatar -->
          <div class="relative flex-shrink-0">
            <div class="w-24 h-24 md:w-32 md:h-32 rounded-full border-4 border-white shadow-lg overflow-hidden bg-white">
              <img
                v-if="user.avatar_url"
                :src="user.avatar_url"
                :alt="user.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gray-200">
                <svg class="w-12 h-12 md:w-16 md:h-16 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                </svg>
              </div>
            </div>
            <!-- VIP badge -->
            <div class="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-yellow-400 text-yellow-900 text-xs font-bold px-2 py-0.5 rounded-full border-2 border-white">
              VIP
            </div>
          </div>

          <!-- User info -->
          <div class="flex-1 pt-2">
            <div class="flex items-center gap-2 flex-wrap mb-2">
              <h1 class="text-2xl md:text-3xl font-bold text-white">{{ user.name }}</h1>
              <span class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs rounded-md border border-white/30">
                Đã xác minh
              </span>
              <span class="px-2 py-1 bg-white/20 backdrop-blur-sm text-white text-xs rounded-md border border-white/30">
                Thành viên Bạc
              </span>
            </div>

            <div class="flex items-center gap-4 flex-wrap text-white/90 text-sm">
              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span>{{ user.location || 'Hà Nội' }}</span>
              </div>

              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
                <span>25+ đơn hàng</span>
              </div>

              <div class="flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span>Tham gia từ {{ user.created_year || '2025' }}</span>
              </div>
            </div>
          </div>

          <!-- Menu button -->
          <button class="flex-shrink-0 w-10 h-10 rounded-lg bg-white/20 backdrop-blur-sm border border-white/30 flex items-center justify-center text-white hover:bg-white/30 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"/>
            </svg>
          </button>
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
          <IntroCard :intro="intro" />
          <RecentOrdersCard :items="recent_bills" />
          <RecentReviewsCard :items="recent_reviews" />
        </div>
      </div>
    </div>
  </div>
</template>
