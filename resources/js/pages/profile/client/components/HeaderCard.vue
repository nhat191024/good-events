<script setup lang="ts">
const props = defineProps<{
  user: {
    name: string
    avatar_url: string
    created_year: string | null
    location?: string | null
  }
}>()
</script>

<template>
  <div class="relative">
    <!-- Adjusted avatar positioning to align with increased padding -->
    <div class="absolute -top-24 left-1/2 -translate-x-1/2">
      <div class="relative w-28 h-28 md:w-32 md:h-32">
        <img
          :src="user.avatar_url"
          :alt="user.name"
          class="w-full h-full rounded-full object-cover ring-4 ring-yellow-400 shadow-lg"
        />
        <!-- Online badge -->
        <div class="absolute bottom-1 right-1 w-6 h-6 bg-yellow-400 rounded-full ring-2 ring-white flex items-center justify-center">
          <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"/>
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- User info -->
    <div class="mt-10 text-center">
      <div class="flex items-center justify-center gap-2 flex-wrap">
        <h2 class="text-xl font-bold text-gray-900">{{ user.name }}</h2>
        <span class="px-2 py-0.5 bg-gray-100 text-gray-700 text-xs rounded-full">Đã xác minh</span>
        <span class="px-2 py-0.5 bg-rose-100 text-rose-700 text-xs rounded-full">Thành viên bạc</span>
      </div>

      <!-- Location and stats -->
      <div class="mt-2 flex items-center justify-center gap-3 text-sm text-gray-600 flex-wrap">
        <div class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          <span>{{ user.location || 'Hà Nội' }}</span>
        </div>
        <div class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
          </svg>
          <span>25+ đơn hàng</span>
        </div>
        <div class="flex items-center gap-1">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span>Tham gia từ {{ user.created_year || '2025' }}</span>
        </div>
      </div>
    </div>
  </div>
</template>
