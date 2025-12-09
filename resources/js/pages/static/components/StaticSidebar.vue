<template>
<aside class="md:sticky md:top-28 md:h-max">
        <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
            <div class="border-b border-slate-100 px-5 py-4">
                <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Mục lục</p>
                <p class="mt-1 text-sm text-slate-600">Chọn mục để chuyển nhanh đến nội dung tương ứng.</p>
            </div>
            <nav class="flex flex-col divide-y divide-slate-100">
                <button
                    v-for="(section, index) in sections"
                    :key="section.id"
                    type="button"
                    class="flex items-start gap-3 px-5 py-4 text-left transition hover:bg-slate-50 focus:outline-none"
                    :class="activeId === section.id ? 'bg-blue-50 text-blue-700' : 'text-slate-700'"
                    @click="$emit('select', section.id)"
                >
                    <span
                        class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold"
                        :class="activeId === section.id ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500'"
                    >
                        {{ index + 1 }}
                    </span>
                    <span class="text-sm leading-6">{{ section.title }}</span>
                </button>
            </nav>
        </div>
    </aside>
</template>

<script setup lang="ts">
import type { StaticSection } from '../types';

defineProps<{
    sections: StaticSection[];
    activeId: string | null;
}>();

defineEmits<{
    select: [string];
}>();
</script>
