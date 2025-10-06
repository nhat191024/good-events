<script setup lang="ts">
import { ref, computed } from 'vue'

const props = defineProps<{
  items: Array<{
    id: number
    code: string
    status: string
    final_total: number | null
    date: string | null
    event: string | null
    category: string | null
    partner_name: string | null
  }>
}>()

const money = (v: number | null) => v != null ? v.toLocaleString('vi-VN') + ' đ' : '—'

const statusUi = (s: string) => {
  switch (s) {
    case 'completed':
      return { text: 'Hoàn thành', cls: 'bg-emerald-50 text-emerald-700 border-emerald-200', icon: '✓' }
    case 'processing':
      return { text: 'Đang xử lý', cls: 'bg-amber-50 text-amber-700 border-amber-200', icon: '⟳' }
    case 'cancelled':
      return { text: 'Thất bại', cls: 'bg-rose-50 text-rose-700 border-rose-200', icon: '✕' }
    default:
      return { text: s, cls: 'bg-gray-50 text-gray-600 border-gray-200', icon: '•' }
  }
}

const showAll = ref(false)
const visibleItems = computed(() => (showAll.value ? props.items : props.items.slice(0, 2)))
</script>

<template>
  <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-rose-600">Đơn hàng gần đây</h3>
      <!-- <button class="text-sm text-rose-600 hover:text-rose-700 font-medium flex items-center gap-1">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Xem thêm
      </button> -->
    </div>

    <div class="space-y-3">
      <div
        v-for="it in visibleItems"
        :key="it.id"
        class="flex items-start gap-4 p-4  "
      >
        <div class="w-12 h-12 rounded-lg bg-rose-100 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
        </div>

        <div class="flex-1 min-w-0">
          <div class="font-semibold text-gray-900">{{ it.category || 'Chủ hề văn bóng' }}</div>
          <div class="text-sm text-gray-600 mt-1">
            {{ it.event || 'Linh vực: Chủ hề' }} • {{ it.date || '15/08/2025' }}
          </div>
          <div class="flex items-center gap-2 mt-2">
            <div class="flex items-center">
              <span v-for="n in 5" :key="n" class="text-yellow-400 text-sm">
                {{ n <= 4 ? '★' : '☆' }}
              </span>
            </div>
          </div>
        </div>

        <div class="text-right flex-shrink-0">
          <div
            :class="['inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium border', statusUi(it.status).cls]"
          >
            <span>{{ statusUi(it.status).icon }}</span>
            {{ statusUi(it.status).text }}
          </div>
          <div class="mt-2 text-lg font-bold text-rose-600">{{ money(it.final_total) }}</div>
        </div>
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
