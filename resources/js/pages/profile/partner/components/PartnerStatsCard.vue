<script setup lang="ts">
const props = defineProps<{ stats: { customers:number; years:number; satisfaction_pct:string; avg_response:string } }>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4 grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
    <div>
      <div class="text-xl font-semibold text-rose-600">{{ stats.customers }}+</div>
      <div class="text-xs text-gray-600">Khách hàng</div>
    </div>
    <div>
      <div class="text-xl font-semibold text-rose-600">{{ stats.years }}</div>
      <div class="text-xs text-gray-600">Năm KN</div>
    </div>
    <div>
      <div class="text-xl font-semibold text-rose-600">{{ stats.satisfaction_pct }}</div>
      <div class="text-xs text-gray-600">Hài lòng</div>
    </div>
    <div>
      <div class="text-xl font-semibold text-rose-600">{{ stats.avg_response }}</div>
      <div class="text-xs text-gray-600">Phản hồi</div>
    </div>
  </div>
</template>
