<script setup lang="ts">
const props = defineProps<{ stats: { orders_placed:number; completed_orders:number; cancelled_orders_pct:string; total_spent:number; last_active_human:string|null } }>()
const money = (v:number) => (v || 0).toLocaleString('vi-VN') + ' đ'
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4 text-sm">
    <div class="flex items-center justify-between">
      <div class="text-gray-600">Tổng chi tiêu</div>
      <div class="font-semibold text-rose-600">{{ money(stats.total_spent) }}</div>
    </div>
    <div class="mt-2 grid grid-cols-2 gap-2">
      <div class="p-2 rounded-md bg-gray-50">
        <div class="text-gray-600">Đơn hàng hoàn thành</div>
        <div class="font-semibold">{{ stats.completed_orders }}</div>
      </div>
      <div class="p-2 rounded-md bg-gray-50">
        <div class="text-gray-600">Tỉ lệ huỷ đơn</div>
        <div class="font-semibold">{{ stats.cancelled_orders_pct }}</div>
      </div>
    </div>
    <div class="mt-2 text-xs text-gray-500">Hoạt động lần cuối {{ stats.last_active_human || '—' }}</div>
  </div>
</template>
