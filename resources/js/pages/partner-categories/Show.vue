<!-- resources/js/pages/partner-categories/Show.vue -->
<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

interface Item {
    id: number;
    name: string;
    slug: string;
    min_price: number | null;
    max_price: number | null;
    description: string | null;
    updated_human?: string | null;
    image: string | null;
}
interface SimpleCat { id: number; name: string; slug: string }
interface Props {
    item: Item;
    category: SimpleCat | null;
    parent: SimpleCat | null;
    related: Array<{
        id: number; name: string; slug: string; min_price: number | null; max_price: number | null; image: string | null
    }>;
}
const props = defineProps<Props>();

const money = (v: number | null | undefined) =>
    typeof v === 'number' ? v.toLocaleString('vi-VN') + ' đ' : 'Liên hệ';

const priceText = computed(() => {
    const { min_price, max_price } = props.item;
    if (min_price && max_price) {
        if (min_price === max_price) return money(min_price);
        return `${money(min_price)} - ${money(max_price)}`;
    }
    if (min_price) return money(min_price);
    if (max_price) return money(max_price);
    return 'Liên hệ';
});

const contactText = 'Liên hệ'


const placeholderImg = computed(() =>
    `https://ui-avatars.com/api/?name=${encodeURIComponent(props.item.name)}&background=ED3B50&color=ffffff&size=256`
);

const breadcrumbs = computed(() => {
    return [
        { label: 'Sự kiện', href: '/' },
        { label: props.category?.name ?? '', href: undefined },
        { label: 'Chi tiết', href: undefined },
    ].filter(b => b.label);
});
</script>

<template>
    <ClientHeaderLayout>
        <div class="min-h-screen bg-white">
            <!-- Breadcrumb (compact) -->
            <div class="mx-4 my-1 px-2 py-1.5 rounded-l bg-white">
                <nav class="text-xs md:text-[13px] text-gray-500">
                    <ul class="flex flex-wrap items-center gap-1">
                        <li v-for="(b, i) in breadcrumbs" :key="i" class="flex items-center gap-1">
                            <template v-if="b.href">
                                <Link :href="b.href" class="hover:text-[#ED3B50]">{{ b.label }}</Link>
                            </template>
                            <template v-else>
                                <span>{{ b.label }}</span>
                            </template>
                            <span v-if="i < breadcrumbs.length - 1" class="opacity-70">›</span>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Hero (compact spacing) -->
            <section class="mx-auto px-4 py-3 md:py-2">
                <div class="grid grid-cols-1 md:grid-cols-12 gap-4 border rounded-2xl p-3 md:p-4 bg-white shadow-md">
                    <!-- Left image + thumbs -->
                    <div class="md:col-span-4">
                        <div
                            class="aspect-square w-full rounded-2xl bg-gray-50 ring-1 ring-gray-200 overflow-hidden flex items-center justify-center">
                            <img :src="props.item.image || placeholderImg" :alt="props.item.name"
                                class="w-3/4 h-3/4 object-contain" />
                        </div>
                        <div class="mt-2 flex items-center gap-2">
                            <div v-for="n in 5" :key="n" class="w-10 h-10 rounded-md bg-gray-100 ring-1 ring-gray-200">
                            </div>
                        </div>
                    </div>

                    <!-- Right info -->
                    <div class="md:col-span-8 flex flex-col gap-1 p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <h1 class="text-xl md:text-2xl font-semibold text-gray-900">{{ props.item.name }}</h1>
                                <div class="text-xs text-gray-500">
                                    {{ props.category?.name }}
                                </div>
                            </div>
                            <button class="px-3 py-1.5 rounded-full ring-1 ring-gray-200 hover:ring-[#ED3B50] text-xs">
                                ♡ Lưu
                            </button>
                        </div>

                        <!-- price -->
                        <div class="mt-1 text-2xl font-semibold text-[#ED3B50]">{{ priceText }}</div>

                        <!-- meta bullets -->
                        <ul class="mt-2 space-y-1.5 text-[14px] md:text-[15px] text-black/80">
                            <li class="flex items-center gap-2">
                                <svg viewBox="0 0 24 24" class="w-4 h-4">
                                    <path d="M12 21s7-5.33 7-11a7 7 0 1 0-14 0c0 5.67 7 11 7 11Z" fill="none"
                                        stroke="currentColor" stroke-width="1.6" />
                                    <circle cx="12" cy="10" r="2.5" fill="none" stroke="currentColor"
                                        stroke-width="1.6" />
                                </svg>
                                <span>Địa chỉ chi tiết ở đây</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <svg viewBox="0 0 24 24" class="w-4 h-4">
                                    <circle cx="12" cy="12" r="9" fill="none" stroke="currentColor"
                                        stroke-width="1.6" />
                                    <path d="M12 7v6l4 2" fill="none" stroke="currentColor" stroke-width="1.6" />
                                </svg>
                                <span>Cập nhật {{ props.item.updated_human || 'gần đây' }}</span>
                            </li>
                        </ul>

                        <!-- actions full-width -->
                        <div class="mt-2 grid grid-cols-2 gap-3">
                            <Link v-if="props.item.slug && props.category?.slug"
                                :href="route('quick-booking.fill-info', { partner_category_slug: props.category?.slug, partner_child_category_slug: props.item.slug })"
                                class="col-span-1 w-full h-14 inline-flex items-center justify-center rounded-xl bg-[#ED3B50] text-white text-[17px] font-medium">
                            Thuê ngay
                            </Link>
                            <Link :href="'#'"
                                class="col-span-1 w-full h-14 inline-flex items-center justify-center rounded-xl border-2 border-black bg-gray-50 text-[17px] font-medium hover:bg-gray-100">
                            Liên hệ
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Mô tả -->
            <section class="mx-auto px-4 mt-1 md:mt-2">
                <div class="border rounded-2xl p-3 md:p-4 bg-white shadow-md">
                    <h2 class="font-semibold mb-2">Mô tả chi tiết</h2>
                    <p class="text-gray-700 whitespace-pre-line">
                        {{ props.item.description || 'Chưa có mô tả.' }}
                    </p>
                </div>
            </section>

            <!-- Thông tin chi tiết -->
            <section class="mx-auto px-4 mt-1 md:mt-4">
                <div class="border rounded-2xl p-3 md:p-4 bg-white shadow-md">
                    <h2 class="font-semibold mb-3">Thông tin chi tiết</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                        <div>
                            <div class="text-gray-500">Loại</div>
                            <div class="font-medium">Sự kiện</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Lĩnh vực</div>
                            <div class="font-medium">{{ props.parent?.name || '—' }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Kiểu đối tác</div>
                            <div class="font-medium">{{ props.item.name }}</div>
                        </div>
                        <div>
                            <div class="text-gray-500">Mã kiểu</div>
                            <div class="font-medium lowercase">{{ props.item.slug }}</div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Liên quan -->
            <section class="mx-auto px-4 mt-1 md:mt-4 pb-8">
                <div class="bg-white min-h-[50rem]">
                    <div class="flex items-center justify-between p-2 bg-white">
                        <h2 class="font-semibold">Liên quan</h2>
                        <div class="text-xs text-gray-500">Tin mới nhất ↓</div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4 mx-4">
                        <Link v-for="r in related" :key="r.id" :href="route('partner-categories.show', r.slug)"
                            class="group">
                        <div
                            class="aspect-square w-full rounded-2xl bg-gray-50 ring-1 ring-gray-200 overflow-hidden flex items-center justify-center shadow-md">
                            <img :src="r.image || `https://ui-avatars.com/api/?name=${encodeURIComponent(r.name)}&background=ED3B50&color=ffffff&size=256`"
                                :alt="r.name" class="w-2/3 h-2/3 object-contain" />
                        </div>
                        <div class="mt-2 text-sm font-medium text-gray-900 group-hover:text-[#ED3B50] line-clamp-1">{{
                            r.name }}
                        </div>
                        <div class="text-xs text-gray-500">
                            {{
                                (r.min_price && r.max_price)
                                    ? (r.min_price === r.max_price ? (r.min_price.toLocaleString('vi-VN') + ' đ') :
                                        (r.min_price.toLocaleString('vi-VN') + ' - ' + r.max_price.toLocaleString('vi-VN') + ' đ'))
                                    :
                                    ((r.min_price || r.max_price) ? ((r.min_price || r.max_price)!.toLocaleString('vi-VN') +' đ')
                            :
                            contactText)
                            }}
                        </div>
                        </Link>
                    </div>
                </div>
            </section>
        </div>
    </ClientHeaderLayout>
</template>
