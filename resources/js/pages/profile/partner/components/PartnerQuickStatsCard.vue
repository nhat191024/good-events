<script setup lang="ts">
const props = defineProps<{ quick: { orders_placed:number; completed_orders:number; cancelled_orders_pct:string; last_active_human:string|null } }>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <!-- Red title and list layout matching design -->
    <h3 class="font-semibold mb-3 text-rose-600">Thống kê nhanh</h3>
    <div class="space-y-3">
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Tổng chi tiêu</span>
        <span class="font-semibold text-rose-600">10.000 đ</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Đơn hàng hoàn thành</span>
        <span class="font-semibold text-gray-900">{{ quick.completed_orders }}</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Tỉ lệ huỷ đơn</span>
        <span class="font-semibold text-gray-900">{{ quick.cancelled_orders_pct }}</span>
      </div>
      <div class="flex justify-between items-center">
        <span class="text-sm text-gray-600">Hoạt động lần cuối</span>
        <span class="font-semibold text-rose-600">{{ quick.last_active_human || '—' }}</span>
      </div>
    </div>
  </div>
</template>
