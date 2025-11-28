<script setup lang="ts">
import { ref, computed } from 'vue'
const props = defineProps<{ items:Array<{ id:number; author:string; rating:number; review:string|null; created_human:string|null }> }>()
const showAll = ref(false)
const visible = computed(() => (showAll.value ? props.items : props.items.slice(0, 2)))

console.log('all the reviews ',visible.value);
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <!-- Red title matching design -->
    <h3 class="font-semibold mb-3 text-rose-600">Đánh giá từ khách hàng</h3>

    <div class="space-y-4">
      <div v-for="it in visible" :key="it.id" class="flex gap-3">
        <!-- Yellow circular avatar -->
        <div class="w-10 h-10 rounded-full bg-yellow-400 flex items-center justify-center flex-shrink-0">
          <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
          </svg>
        </div>

        <div class="flex-1">
          <div class="flex items-center gap-2 text-sm mb-1">
            <span class="font-medium text-gray-900">{{ it.author }}</span>
            <span class="text-gray-500">• {{ it.created_human }}</span>
          </div>

          <!-- Star rating -->
          <div class="flex gap-0.5 mb-2">
            <template v-for="n in 5" :key="n">
              <svg class="w-4 h-4" :class="n <= (it.rating ?? 0) ? 'text-yellow-400 fill-current' : 'text-gray-300 fill-current'" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
              </svg>
            </template>
          </div>

          <p class="text-gray-700 leading-relaxed">{{ it.review }}</p>
        </div>
      </div>
    </div>

    <!-- Full-width button at bottom with icon -->
    <button @click="showAll = !showAll" class="w-full mt-4 py-2.5 rounded-lg border border-gray-200 hover:bg-gray-50 transition-colors flex items-center justify-center gap-2 text-sm font-medium text-gray-700">
      <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
      </svg>
      {{ showAll ? 'Thu gọn' : 'Xem tất cả đánh giá' }}
    </button>
  </div>
</template>
