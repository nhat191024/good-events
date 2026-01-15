<script setup lang="ts">
import { computed } from 'vue'

const props = defineProps<{ iframe: string | null }>()

const hasIframe = computed(() => Boolean((props.iframe ?? '').trim()))
</script>

<template>
  <div class="bg-white rounded-2xl shadow-sm ring-1 ring-gray-200 p-4">
    <h3 class="font-semibold text-rose-600 mb-3">Video giới thiệu</h3>

    <div v-if="hasIframe" class="relative overflow-hidden rounded-xl bg-black/5">
      <div class="aspect-video video-embed" v-html="props.iframe"></div>
    </div>

    <p v-else class="text-gray-500">Chưa có video giới thiệu.</p>
  </div>
</template>

<style scoped>
.video-embed {
  width: 100%;
}

.video-embed :deep(iframe),
.video-embed :deep(embed),
.video-embed :deep(video),
.video-embed :deep(object) {
  width: 100% !important;
  height: 100% !important;
  display: block;
  border: 0;
}
</style>
