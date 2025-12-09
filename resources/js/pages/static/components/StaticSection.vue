<template>
    <section
        :id="section.id"
        :data-section-id="section.id"
        class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 md:p-8"
    >
        <div class="flex items-start gap-4">
            <span class="mt-1 flex h-9 w-9 flex-none items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-700">
                {{ order }}
            </span>
            <div class="space-y-3">
                <div>
                    <h2 class="text-xl font-semibold text-slate-900">{{ section.title }}</h2>
                    <p v-if="section.summary" class="mt-1 text-sm text-slate-600">
                        {{ section.summary }}
                    </p>
                </div>

                <div class="space-y-3 text-slate-700">
                    <div v-for="(block, blockIndex) in section.blocks" :key="`${section.id}-${blockIndex}`">
                        <p v-if="block.type === 'paragraph'" class="leading-7">{{ block.text }}</p>

                        <div v-else-if="block.type === 'subheading'" class="mt-4 text-base font-semibold text-slate-900">
                            {{ block.text }}
                        </div>

                        <div v-else-if="block.type === 'list'" class="space-y-2">
                            <p v-if="block.title" class="text-sm font-semibold text-slate-800">{{ block.title }}</p>
                            <ul class="space-y-1">
                                <li v-for="(item, itemIndex) in block.items" :key="`${section.id}-${blockIndex}-${itemIndex}`" class="flex gap-2 leading-7">
                                    <span class="mt-2 h-1.5 w-1.5 flex-none rounded-full bg-blue-600"></span>
                                    <span class="text-slate-700">{{ item }}</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import type { StaticSection } from '../types';

defineProps<{
    section: StaticSection;
    order: number;
}>();
</script>
