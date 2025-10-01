<template>
  <Link :href="route('partner-categories.show', partnerCategory.slug)" class="block">
    <div class="bg-white rounded-lg shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow duration-200">
      <!-- Heart icon -->
      <div class="relative">
        <div class="absolute top-3 right-3 z-10">
          <button class="p-1 rounded-full bg-white/80 hover:bg-white transition-colors">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
          </button>
        </div>
        <!-- Placeholder image -->
        <div class="aspect-square bg-gradient-to-br from-red-50 to-orange-50 flex items-center justify-center">
          <img
            :src="partnerCategory.image || `/placeholder.svg?height=200&width=200&query=${encodeURIComponent(partnerCategory.name)}`"
            :alt="partnerCategory.name"
            class="w-full h-full object-cover"
          />
        </div>
      </div>
      <!-- Content -->
      <div class="p-4">
        <h3 class="font-semibold text-gray-900 text-sm mb-1">
          {{ partnerCategory.name }}
        </h3>
        <p class="text-gray-500 text-xs mb-3 line-clamp-2">
          {{ partnerCategory.description || 'Chuyên múa lân rết' }}
        </p>
        <!-- Price range -->
        <div class="text-red-500 font-semibold text-sm">
          {{ formatPrice(partnerCategory.min_price || 0) }} - {{ formatPrice(partnerCategory.max_price || 0) }}
        </div>
      </div>
    </div>
  </Link>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { PartnerCategory } from '@/types/database';

interface Props {
  partnerCategory: PartnerCategory & { image?: string | null };
}

const props = defineProps<Props>();

const formatPrice = (price: number): string => {
  return new Intl.NumberFormat('vi-VN', {
    style: 'currency',
    currency: 'VND',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(price).replace('₫', 'đ');
};
</script>
