<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{
  intro: string | null
  stats?: {
    orders_placed?: number | null
    avg_rating?: number | null
  }
}>()

const introText = computed(() => props.intro || 'Chưa có nội dung giới thiệu.')
const ordersPlaced = computed(() => props.stats?.orders_placed ?? 0)
const averageRating = computed(() => {
  const value = props.stats?.avg_rating
  return typeof value === 'number' ? value : null
})
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-bold text-rose-600 mb-3">Giới thiệu</h3>

    <p class="text-gray-700 leading-relaxed mb-6" v-html="introText ?? 'Chưa có nội dung giới thiệu.'"></p>

    <!-- Stats -->
    <div class="grid grid-cols-2 gap-4">
      <div class="text-center p-4">
        <div class="text-2xl font-bold text-rose-600">{{ ordersPlaced }}+</div>
        <div class="text-sm text-gray-600 mt-1">Đơn hàng</div>
      </div>
      <div class="text-center p-4 ">
        <div class="text-2xl font-bold text-rose-600">{{ averageRating !== null ? averageRating.toFixed(1) : '—' }}
        </div>
        <div class="text-sm text-gray-600 mt-1">Đánh giá TB</div>
      </div>
    </div>
  </div>
</template>
