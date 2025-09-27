<script setup lang="ts">
const props = defineProps<{ items: Array<{ id:number; code:string; status:string; final_total:number|null; date:string|null; event:string|null; category:string|null; partner_name:string|null }> }>()
const money = (v:number|null) => v!=null ? v.toLocaleString('vi-VN') + ' đ' : '—'
const statusUi = (s:string) => {
  switch (s) {
    case 'completed': return { text: 'Hoàn thành', cls: 'bg-emerald-100 text-emerald-700' }
    case 'processing': return { text: 'Đang xử lý', cls: 'bg-amber-100 text-amber-700' }
    case 'cancelled': return { text: 'Thất bại', cls: 'bg-rose-100 text-rose-700' }
    default: return { text: s, cls: 'bg-gray-100 text-gray-600' }
  }
}
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <div class="flex items-center justify-between mb-2">
      <h3 class="font-semibold">Đơn hàng gần đây</h3>
      <a href="#" class="text-sm text-rose-600 hover:underline">Xem thêm</a>
    </div>

    <div class="divide-y">
      <div v-for="it in items" :key="it.id" class="py-3 flex items-start justify-between gap-4">
        <div>
          <div class="font-medium">{{ it.category || '—' }}</div>
          <div class="text-xs text-gray-500">{{ it.event || '—' }} • {{ it.date || '' }}</div>
        </div>
        <div class="text-right">
          <div :class="['inline-flex items-center px-2 py-0.5 rounded-full text-xs', statusUi(it.status).cls]">
            {{ statusUi(it.status).text }}
          </div>
          <div class="mt-1 text-sm text-rose-600 font-semibold">{{ money(it.final_total) }}</div>
        </div>
      </div>
    </div>
  </div>
</template>
