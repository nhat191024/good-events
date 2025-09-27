<script setup lang="ts">
const props = defineProps<{ intro: string | null }>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <h3 class="font-semibold">Giới thiệu</h3>
    <p class="mt-2 text-gray-700 whitespace-pre-line">
      {{ intro || 'Chưa có mô tả.' }}
    </p>
  </div>
</template>
