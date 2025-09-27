<script setup lang="ts">
const props = defineProps<{ items: Array<{ id:number; subject_name:string; department:string; review:string|null; overall:number|null; created_human:string|null }> }>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-semibold">Đánh giá công khai gần đây</h3>
      <a href="#" class="text-sm text-rose-600 hover:underline">Xem tất cả đánh giá</a>
    </div>

    <div v-if="items.length === 0" class="text-sm text-gray-500">Chưa có đánh giá nào.</div>

    <div v-for="it in items" :key="it.id" class="py-3 border-t first:border-t-0">
      <div class="flex items-center gap-2 text-sm">
        <div class="font-medium">{{ it.subject_name }}</div>
        <span class="text-gray-400">•</span>
        <div class="text-gray-500">{{ it.department }}</div>
        <span class="text-gray-400">•</span>
        <div class="text-gray-500">{{ it.created_human }}</div>
      </div>

      <!-- Stars -->
    <div class="mt-1 flex">
      <span v-for="n in 5" :key="n" class="text-yellow-400">
        {{ n <= (it.overall ?? 0) ? '★' : '☆' }}
      </span>
    </div>

      <p class="mt-1 text-gray-700">{{ it.review }}</p>
    </div>
  </div>
</template>
