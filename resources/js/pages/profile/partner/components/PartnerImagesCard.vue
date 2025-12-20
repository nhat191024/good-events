<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';

type Media = { id: number; url: string }
type Service = { id: number; name: string | null; field: string | null; media: Media[] }

const props = defineProps<{
  services: Service[]
}>()
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <h3 class="font-semibold mb-3 text-rose-600">Hình ảnh dịch vụ</h3>
    <div v-if="props.services.length" class="space-y-4">
      <div v-for="service in props.services" :key="service.id" class="space-y-2">
        <div class="flex items-center justify-between gap-2">
          <span class="font-medium text-gray-900">{{ service.name ?? '—' }}</span>
          <span v-if="service.field" class="text-xs text-gray-500">{{ service.field }}</span>
        </div>
        <div v-if="service.media.length" class="grid grid-cols-2 sm:grid-cols-3 gap-2">
          <ImageWithLoader v-for="media in service.media.slice(0, 10)" :key="media.id" :src="getImg(media.url)"
            :alt="service.name ?? 'Service image'" class="w-full aspect-square rounded-lg"
            img-class="w-full aspect-square object-cover rounded-lg border border-gray-200" loading="lazy" />
        </div>
        <p v-else class="text-sm text-muted-foreground">Chưa có hình ảnh nào.</p>
      </div>
    </div>
    <p v-else class="text-sm text-muted-foreground">Không có dịch vụ nào.</p>
  </div>
</template>
