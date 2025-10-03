<script setup lang="ts">
const props = defineProps<{
  intro:string|null;
  stats: { customers:number; years:number; satisfaction_pct:string; avg_response:string }
}>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <!-- Red title matching design -->
    <h3 class="font-semibold text-rose-600 mb-3">Giới thiệu</h3>
    <p class="text-gray-700 leading-relaxed whitespace-pre-line mb-4">{{ intro || 'Chưa có mô tả.' }}</p>

    <!-- Stats grid below description -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4 border-t">
      <div class="text-center">
        <div class="text-2xl font-bold text-rose-600">{{ stats.customers }}+</div>
        <div class="text-sm text-gray-600 mt-1">Khách hàng</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-rose-600">{{ stats.years }}</div>
        <div class="text-sm text-gray-600 mt-1">Năm KN</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-rose-600">{{ stats.satisfaction_pct }}</div>
        <div class="text-sm text-gray-600 mt-1">Hài lòng</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-rose-600">{{ stats.avg_response }}</div>
        <div class="text-sm text-gray-600 mt-1">Phản hồi</div>
      </div>
    </div>
  </div>
</template>
