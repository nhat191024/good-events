<template>
    <section v-if="items.length" class="space-y-5 rounded-3xl border border-gray-100 bg-white p-6 shadow-sm lg:p-10 md:px-6 md:py-6 px-0 py-0">
        <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h3 class="text-xl font-semibold text-gray-900">Có thể bạn cũng thích</h3>
                <p class="text-sm text-gray-500">Những gợi ý phù hợp với nhu cầu thiết kế của bạn.</p>
            </div>
            <Link :href="route('asset.discover')" class="text-sm font-semibold text-primary-600 hover:text-primary-700">
                Xem tất cả thiết kế →
            </Link>
        </header>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="item in normalizedItems"
                :key="item.id"
                class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xs transition hover:-translate-y-1 hover:shadow-md"
            >
                <CardItem :card-item="item.card" :route-href="item.href" />
                <div class="flex flex-1 flex-col gap-2 px-4 py-4">
                    <h4 class="line-clamp-2 text-base font-semibold text-gray-900">{{ item.name }}</h4>
                    <p class="text-sm text-gray-500">{{ item.categoryName }}</p>
                    <div class="mt-auto flex items-center justify-between pt-2">
                        <span class="text-sm font-semibold text-primary-700">{{ item.priceText }}</span>
                        <Link :href="item.href" class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                            Chi tiết
                        </Link>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

import CardItem from '@/pages/home/components/CardItem/index.vue';
import { formatPrice } from '@/lib/helper';

import type { FileProduct } from '@/pages/home/types';

interface Props {
    items: FileProduct[];
}

const props = withDefaults(defineProps<Props>(), {
    items: () => [],
});

const normalizedItems = computed(() =>
    props.items.map((item) => {
        const priceNumber = Number(item.price);
        const priceText = Number.isFinite(priceNumber) ? `${formatPrice(priceNumber)} đ` : 'Liên hệ';

        const hasCategory = Boolean(item.category?.slug);
        const href = hasCategory
            ? route('asset.show', {
                  category_slug: item.category?.slug,
                  file_product_slug: item.slug,
              })
            : '#';

        return {
            id: item.id,
            name: item.name,
            categoryName: item.category?.name ?? 'Thiết kế sự kiện',
            priceText,
            href,
            card: {
                id: item.id,
                name: item.name,
                slug: item.slug,
                image: item.image,
                description: item.description,
            },
        };
    })
);
</script>
