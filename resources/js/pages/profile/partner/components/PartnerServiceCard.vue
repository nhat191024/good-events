<script setup lang="ts">
import { ref, computed } from 'vue'

type Media = { id: number; url: string }
const props = defineProps<{ services: Array<{ id:number; name:string; field:string; price:number|null, media: Media[] }> }>()
const money = (v:number|null) => v!=null ? v.toLocaleString('vi-VN') + 'đ/giờ' : 'Liên hệ'

const showAll = ref(false)
const visible = computed(() => (showAll.value ? props.services : props.services.slice(0, 2)))
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <h3 class="font-semibold mb-3 text-rose-600">Dịch vụ</h3>
    <div class="space-y-3">
      <div v-for="s in visible" :key="s.id" class="flex justify-between items-start py-2 border-b last:border-b-0">
        <div>
          <div class="font-medium text-gray-900">{{ s.name }}</div>
          <div class="text-sm text-gray-500 mt-0.5">Lĩnh vực: {{ s.field }}</div>
        </div>
        <div class="font-semibold text-rose-600 text-sm whitespace-nowrap ml-4">{{ money(s.price) }}</div>
      </div>
    </div>
    <button @click="showAll = !showAll" class="w-full mt-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      {{ showAll ? 'Thu gọn' : 'Xem thêm' }}
    </button>
  </div>
</template>
