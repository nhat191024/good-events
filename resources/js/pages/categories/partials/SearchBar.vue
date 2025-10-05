<script setup lang="ts">
import { ref, watch, onMounted } from 'vue';

const props = defineProps<{
  modelValue?: string;
}>();
const emit = defineEmits<{
  (e: 'update:model-value', value: string): void;
}>();

const q = ref(props.modelValue ?? '');

// Debounce nhẹ để UX mượt
let t: number | undefined;
watch(q, (val) => {
  if (t) window.clearTimeout(t);
  t = window.setTimeout(() => {
    emit('update:model-value', val);
  }, 250);
});

onMounted(() => {
  q.value = props.modelValue ?? '';
});
</script>

<template>
  <div class="bg-white rounded-xl w-full px-2 md:p-4">
    <div class="flex items-stretch gap-2">
      <div class="flex-1 min-w-0 flex items-center gap-2 px-3 md:px-4">
        <span aria-hidden="true">🔍</span>
        <input
          v-model="q"
          type="text"
          placeholder="Tìm sản phẩm…"
          class="bg-transparent flex-1 min-w-0 py-1 outline-none placeholder:text-gray-400"
        />
      </div>
      <button
        class="rounded-xl w-auto flex-shrink-0 px-2 sm:px-4 py-1 my-2 sm:py-3 sm:my-0 bg-[#ED3B50] text-white font-medium hover:opacity-90 transition-colors"
        @click="$emit('update:model-value', q)"
      >
        Tìm kiếm
      </button>
    </div>
  </div>
</template>
