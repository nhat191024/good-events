<script setup lang="ts">
const props = defineProps<{
  stats: {
    orders_placed: number
    completed_orders: number
    cancelled_orders_pct: string
    total_spent: number
    last_active_human: string | null
  }
}>()

const money = (v: number) => (v || 0).toLocaleString('vi-VN') + ' đ'
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
    <h3 class="font-semibold text-rose-600 mb-3">Thông kê nhanh</h3>

    <!-- Total spent -->
    <div class="flex justify-between items-center mb-3 pb-3 border-b border-gray-200">
      <span class="text-sm text-gray-600">Tổng chi tiêu</span>
      <span class="text-lg font-bold text-rose-600">{{ money(stats.total_spent) }}</span>
    </div>

    <!-- Stats grid -->
    <div class="space-y-2 mb-3">
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Đơn hàng hoàn thành</span>
        <span class="font-semibold text-gray-900">{{ stats.completed_orders }}/{{ stats.orders_placed }}</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Tỉ lệ huỷ đơn</span>
        <span class="font-semibold text-gray-900">{{ stats.cancelled_orders_pct }}</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Hoạt động lần cuối</span>
        <span class="font-semibold text-rose-600">{{ stats.last_active_human || '10 phút trước' }}</span>
      </div>
    </div>
  </div>
</template>
