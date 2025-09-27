<script setup lang="ts">
import HeaderCard from './components/HeaderCard.vue'
import ContactCard from './components/ContactCard.vue'
import QuickStatsCard from './components/QuickStatsCard.vue'
import IntroCard from './components/IntroCard.vue'
import RecentOrdersCard from './components/RecentOrdersCard.vue'
import RecentReviewsCard from './components/RecentReviewsCard.vue'

interface UserInfo {
  id:number; name:string; avatar_url:string; email:string; phone:string;
  created_year:string|null; location:string|null;
}
interface BillItem {
  id:number; code:string; status:string; final_total:number|null; date:string|null;
  event:string|null; category:string|null; partner_name:string|null;
}
interface ReviewItem {
  id:number; subject_name:string; department:string; review:string|null;
  overall:number|null; created_human:string|null;
}
interface Props {
  user: UserInfo;
  stats: { orders_placed:number; completed_orders:number; cancelled_orders_pct:string; total_spent:number; last_active_human:string|null; }
  recent_bills: BillItem[];
  recent_reviews: ReviewItem[];
  intro: string|null;
}
const props = defineProps<Props>();
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- Banner giả để giống hình -->
    <div class="h-40 md:h-52 bg-gradient-to-r from-rose-300 to-rose-500 rounded-b-2xl"></div>

    <div class="container mx-auto px-4 -mt-14 md:-mt-16">
      <div class="grid grid-cols-1 md:grid-cols-12 gap-4 md:gap-6">
        <!-- Left column -->
        <div class="md:col-span-3 space-y-4">
          <HeaderCard :user="user" />
          <ContactCard :user="user" />
          <QuickStatsCard :stats="stats" />
        </div>

        <!-- Right column -->
        <div class="md:col-span-9 space-y-4">
          <IntroCard :intro="intro" />
          <RecentOrdersCard :items="recent_bills" />
          <RecentReviewsCard :items="recent_reviews" />
        </div>
      </div>
    </div>
  </div>
</template>
