<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  items: Array<{
    id: number
    subject_name: string
    department: string
    review: string | null
    overall: number | null
    created_human: string | null
  }>
}>()

const showAll = ref(false)
const visibleItems = computed(() => (showAll.value ? props.items : props.items.slice(0, 2)))
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-rose-600">Đánh giá công khai gần đây</h3>
      <!-- <button class="text-sm text-rose-600 hover:text-rose-700 font-medium flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
        </svg>
        Xem tất cả đánh giá
      </button> -->
    </div>

    <div v-if="items.length === 0" class="text-center py-8 text-gray-500">
      Chưa có đánh giá nào.
    </div>

    <div class="space-y-4">
      <div
        v-for="it in visibleItems"
        :key="it.id"
        class="flex gap-4 p-4 bg-gray-50 rounded-lg"
      >
        <div class="w-12 h-12 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
          </svg>
        </div>

        <div class="flex-1 min-w-0">
          <div class="flex items-center gap-2 flex-wrap">
            <span class="font-semibold text-gray-900">{{ it.subject_name }}</span>
            <span class="text-gray-400">•</span>
            <span class="text-sm text-gray-600">{{ it.department }}</span>
            <span class="text-gray-400">•</span>
            <span class="text-sm text-gray-500">{{ it.created_human || '1 tháng trước' }}</span>
          </div>

          <div class="flex items-center gap-1 mt-1">
            <span v-for="n in 5" :key="n" class="text-yellow-400 text-lg">
              {{ n <= (it.overall ?? 0) ? '★' : '☆' }}
            </span>
          </div>

          <p class="mt-2 text-gray-700 leading-relaxed">
            {{ it.review || 'Rất hài lòng với chất lượng dịch vụ. Giá cả hợp lý và thái độ phục vụ tốt.' }}
          </p>
        </div>
      </div>
    </div>
    <button @click="showAll = !showAll" class="w-full mt-4 py-2.5 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors flex items-center justify-center gap-2">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
      </svg>
      {{ showAll ? 'Thu gọn' : 'Xem tất cả đánh giá' }}
    </button>
  </div>
</template>
