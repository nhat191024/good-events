<script setup lang="ts">
import { Search } from 'lucide-vue-next';
import { debounce } from '@/pages/orders/helper';
import { ref, watch, onMounted } from 'vue';

const props = withDefaults(defineProps<{
    modelValue?: string;
    showSearchBtn?: boolean
}>(), {
    showSearchBtn: true
});

const emit = defineEmits<{
    (e: 'update:model-value', value: string): void;
    (e: 'search', value: string): void;
}>();

const q = ref(props.modelValue ?? '');

// Debounce nhẹ để UX mượt
let t: number | undefined;
watch(q, debounce((val) => {
    emit('update:model-value', val);
}, 500, { leading: false, trailing: true }));

onMounted(() => {
    q.value = props.modelValue ?? '';
});

const submitSearch = () => {
    emit('update:model-value', q.value);
    emit('search', q.value);
};
</script>

<template>
    <div class="bg-white shadow-sm rounded-xl w-full max-w-6xl px-2 md:px-4 py-2 ring ring-gray-300">
        <div class="flex items-stretch gap-2">
            <div class="flex-1 min-w-0 flex items-center gap-2 px-3 md:px-4">
                <Search />
                <input v-model="q" type="text" placeholder="Tìm kiếm..."
                    class="bg-transparent flex-1 min-w-0 py-1 outline-none placeholder:text-gray-400"
                    @keydown.enter.prevent="submitSearch" />
            </div>
            <button v-if="showSearchBtn"
                class="rounded-xl w-auto flex-shrink-0 px-2 sm:px-4 py-1 my-2 sm:py-3 sm:my-0 bg-[#ED3B50] text-white font-medium hover:opacity-90 transition-colors"
                @click="submitSearch">
                Tìm kiếm
            </button>
        </div>
    </div>
</template>
