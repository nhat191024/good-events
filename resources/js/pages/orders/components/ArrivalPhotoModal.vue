<template>
  <div>
    <button
      v-if="props.arrivalPhoto && props.showThumbnail"
      @click="is_modal_open = true"
      class="w-full mt-4 p-3 rounded-lg border-2 border-dashed border-primary/40 bg-primary/5 hover:bg-primary/10 transition-colors flex items-center gap-2"
    >
      <img
        :src="thumbnailSrc"
        :alt="props.altText"
        class="w-12 h-12 rounded object-cover"
        loading="lazy"
      />
      <div class="text-left">
        <p class="text-md font-semibold text-foreground">Ảnh đã đến nơi</p>
        <p class="text-xs text-muted-foreground">Bấm để xem ảnh</p>
      </div>
    </button>

    <!-- Modal Overlay -->
    <div
      v-if="is_modal_open"
      @click="is_modal_open = false"
      class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4"
    >
      <div
        @click.stop
        class="relative max-w-5xl w-full max-h-[90vh] rounded-lg overflow-hidden bg-black"
      >
        <button
          @click="is_modal_open = false"
          class="absolute top-3 right-3 z-10 p-2 rounded-full bg-black/50 hover:bg-black/70 transition-colors"
        >
          <svg
            class="w-6 h-6 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>

        <!-- Image -->
        <img
          :src="modalSrc"
          :alt="props.altText"
          class="w-full h-full object-contain"
          loading="lazy"
        />

        <!-- Info Footer -->
        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/100 to-black/50 p-4">
          <p class="text-white text-sm font-medium">{{ props.altText }}</p>
          <p class="text-white/70 text-xs mt-1">Ảnh bằng chứng đã đến nơi</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';
import { ref, computed } from 'vue'

const props = withDefaults(defineProps<{
  arrivalPhoto?: string | null
  altText?: string
  showThumbnail?: boolean
}>(), {
  arrivalPhoto: null,
  altText: 'Arrival Photo',
  showThumbnail: true,
})

const is_modal_open = ref(false)

const thumbnailSrc = computed(() => getImg(props.arrivalPhoto))
const modalSrc = computed(() => getImg(props.arrivalPhoto))
</script>
