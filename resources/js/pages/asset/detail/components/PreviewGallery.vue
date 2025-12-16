<template>
    <section v-if="media.length" class="space-y-4 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm lg:p-10">
        <header class="space-y-1">
            <h3 class="text-xl font-semibold text-gray-900">Hình ảnh xem trước</h3>
            <p class="text-sm text-gray-500">Một số mẫu layout nổi bật trong gói thiết kế.</p>
        </header>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <figure
                v-for="item in media"
                :key="item.id ?? item.url"
                class="relative overflow-hidden rounded-3xl border border-gray-100 bg-gray-50"
            >
                <img
                    :src="getImg(item.url)"
                    :alt="item.alt ?? 'Xem trước thiết kế'"
                    class="h-full w-full object-cover transition duration-300 hover:scale-[1.02]"
                    loading="lazy"
                />
            </figure>
        </div>
    </section>
</template>

<script setup lang="ts">
import { getImg } from '@/pages/booking/helper';

interface PreviewMedia {
    id?: number | string | null;
    url: string;
    alt?: string | null;
}

interface Props {
    media: PreviewMedia[];
}

const props = withDefaults(defineProps<Props>(), {
    media: () => [],
});
</script>
