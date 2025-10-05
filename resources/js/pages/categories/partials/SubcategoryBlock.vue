<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Item {
  id: number;
  name: string;
  slug: string;
  image: string; // đã normalize là placeholder nếu null
}

const props = defineProps<{
  title: string;
  items: Item[];
}>();
</script>

<template>
  <section>
    <!-- Tiêu đề danh mục con -->
    <div class="flex items-center justify-between mb-4">
      <h2 class="text-lg md:text-xl font-semibold text-gray-900">{{ title }}</h2>
    </div>

    <!-- Grid items -->
    <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 lg:grid-cols-8 gap-6">
      <Link
        v-for="it in items"
        :key="it.id"
        :href="route('partner-categories.show', it.slug)"
        class="group flex flex-col items-center text-center"
      >
        <div class="w-14 h-14 md:w-16 md:h-16 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden ring-1 ring-gray-200 group-hover:ring-[#ED3B50] transition">
          <img :src="it.image" :alt="it.name" class="w-full h-full object-cover" />
        </div>
        <div class="mt-2 text-xs md:text-sm text-gray-700 group-hover:text-[#ED3B50] line-clamp-1">
          {{ it.name }}
        </div>
      </Link>
    </div>

    <!-- Divider mảnh -->
    <div class="mt-6 h-px w-full bg-gray-200/70"></div>
  </section>
</template>
