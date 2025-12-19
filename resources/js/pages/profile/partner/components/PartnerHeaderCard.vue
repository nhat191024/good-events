<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';

const props = defineProps<{
  user: { name:string; avatar_url:string; location:string|null; joined_year:string|null; is_pro:boolean; rating:number; total_reviews:number; total_customers:number|null }
}>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <div class="flex items-center gap-4">
      <img :src="getImg(user.avatar_url)" :alt="user.name" class="w-20 h-20 rounded-full object-cover ring-2 ring-white" loading="lazy" />
      <div class="flex-1">
        <div class="flex items-center gap-2">
          <h1 class="text-xl font-semibold">{{ user.name }}</h1>
          <!-- <span v-if="user.is_pro" class="px-2 py-0.5 bg-rose-600 text-white text-xs rounded">PRO</span> -->
        </div>
        <div class="flex items-center gap-1 text-yellow-500 text-sm">
          <template v-for="n in 5" :key="n">
            <span>{{ n <= Math.round(user.rating) ? '★' : '☆' }}</span>
          </template>
          <span class="text-gray-600 ml-1">{{ user.rating.toFixed(1) }} ({{ user.total_reviews }} đánh giá)</span>
        </div>
        <div class="text-sm text-gray-500">
          {{ user.location || '—' }} • {{ user.total_customers || '—' }}+ khách hàng • Tham gia từ {{ user.joined_year || '—' }}
        </div>
      </div>
    </div>
  </div>
</template>
