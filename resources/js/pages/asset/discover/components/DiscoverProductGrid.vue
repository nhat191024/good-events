<template>
    <section>
        <div
            v-if="!products.length"
            class="rounded-2xl border border-dashed border-gray-200 bg-white px-6 py-16 text-center text-sm text-gray-500"
        >
            Không tìm thấy tài liệu nào khớp với bộ lọc hiện tại.
        </div>

        <div v-else class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <article
                v-for="item in products"
                :key="item.id"
                class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:-translate-y-1 hover:shadow-md"
            >
                <div class="relative">
                    <CardItem :show-info="false" :card-item="item.card" :route-href="item.href" />
                    <span
                        v-if="item.categoryName"
                        class="absolute left-3 top-3 inline-flex items-center rounded-full bg-white/90 px-3 py-1 text-xs font-medium text-primary-700 shadow-sm"
                    >
                        {{ item.categoryName }}
                    </span>
                </div>
                <div class="flex flex-1 flex-col gap-2 px-5 py-4">
                    <h2 class="line-clamp-2 text-base font-semibold text-gray-900">
                        {{ item.name }}
                    </h2>
                    <p class="line-clamp-2 text-sm text-gray-500">
                        {{ item.description || 'Bộ tài liệu thiết kế chất lượng cao cho sự kiện của bạn.' }}
                    </p>
                    <div class="mt-auto flex items-center justify-between pt-2">
                        <span class="text-lg font-semibold text-primary-700">{{ item.priceText }}</span>
                        <Link
                            v-if="item.hasValidRoute"
                            :href="item.href"
                            class="text-sm font-semibold text-primary-600 transition hover:text-primary-700"
                        >
                            Xem chi tiết →
                        </Link>
                    </div>
                </div>
            </article>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import CardItem from '@/pages/home/components/CardItem/index.vue';

interface CardItemProps {
    id: number;
    name: string;
    slug: string;
    image?: string | null;
    description?: string | null;
}

export interface DiscoverProductListItem {
    id: number;
    name: string;
    description: string | null;
    categoryName: string | null;
    priceText: string;
    href: string;
    hasValidRoute: boolean;
    card: CardItemProps;
}

interface Props {
    products: DiscoverProductListItem[];
}

defineProps<Props>();
</script>
