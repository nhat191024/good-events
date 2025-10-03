<script setup lang="ts">
const props = defineProps<{
  intro: string | null
  stats?: {
    orders_placed?: number
    designs_count?: number
    avg_rating?: number
  }
}>()
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <h3 class="text-lg font-bold text-rose-600 mb-3">Giới thiệu</h3>

    <p class="text-gray-700 leading-relaxed mb-6">
      {{ intro || 'Mình là Thịnh, mọi người hỏi xem qua nhé. Với hơn 5 năm kinh nghiệm trong lĩnh vực tổ chức sự kiện, tôi cam kết mang đến những trải nghiệm tốt nhất cho khách hàng.' }}
    </p>

    <!-- Stats -->
    <div class="grid grid-cols-3 gap-4">
      <div class="text-center p-4">
        <div class="text-2xl font-bold text-rose-600">{{ stats?.orders_placed || 23 }}+</div>
        <div class="text-sm text-gray-600 mt-1">Đơn hàng</div>
      </div>
      <div class="text-center p-4 ">
        <div class="text-2xl font-bold text-rose-600">{{ stats?.designs_count || 2 }}</div>
        <div class="text-sm text-gray-600 mt-1">Mẫu thiết kế</div>
      </div>
      <div class="text-center p-4 ">
        <div class="text-2xl font-bold text-rose-600">{{ stats?.avg_rating || 4.5 }}</div>
        <div class="text-sm text-gray-600 mt-1">Đánh giá TB</div>
      </div>
    </div>
  </div>
</template>
