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
  <div class="bg-white rounded-xl w-auto p-2 md:w-full">
    <div class="flex items-stretch gap-2">
      <div class="flex-1 flex items-center gap-2 px-4">
        <span aria-hidden="true">🔍</span>
        <input
          v-model="q"
          type="text"
          placeholder="Tìm sản phẩm…"
          class="bg-transparent flex-1 py-2 outline-none placeholder:text-gray-400"
        />
      </div>
      <button
        class="rounded-xl px-30 py-2 bg-[#ED3B50] text-white font-medium hover:opacity-90 transition"
        @click="$emit('update:model-value', q)"
      >
        Tìm kiếm
      </button>
    </div>
  </div>
</template>
