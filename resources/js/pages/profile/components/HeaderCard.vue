<script setup lang="ts">
const props = defineProps<{ user: { name:string; avatar_url:string; created_year:string|null } }>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <div class="flex items-center gap-3">
      <img :src="user.avatar_url" :alt="user.name" class="w-16 h-16 rounded-full ring-2 ring-white object-cover" />
      <div>
        <div class="text-lg font-semibold">{{ user.name }}</div>
        <div class="text-xs text-gray-500">Tham gia từ {{ user.created_year || '—' }}</div>
      </div>
    </div>
  </div>
</template>
