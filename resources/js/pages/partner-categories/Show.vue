<!-- resources/js/pages/partner-categories/Show.vue -->
<template>

    <Head :title="pageTitle" />

    <ClientHeaderLayout>
        <section class="w-full bg-white pb-16 pt-6">
            <div class="mx-auto flex w-full max-w-6xl flex-col gap-3 md:gap-10 px-2 md:px-4 sm:px-6 lg:px-8">
                <!-- Breadcrumb -->
                <nav aria-label="Breadcrumb" class="text-xs font-medium uppercase tracking-wide text-primary-600">
                    <ul class="flex flex-wrap items-center gap-2">
                        <li>
                            <Link href="/" class="hover:text-primary-800">Sự kiện</Link>
                        </li>
                        <li v-if="props.category" class="flex items-center gap-2">
                            <span aria-hidden="true" class="text-primary-400">›</span>
                            <span class="text-primary-800">{{ props.category.name }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <span aria-hidden="true" class="text-primary-400">›</span>
                            <span class="text-primary-800">Chi tiết</span>
                        </li>
                    </ul>
                </nav>

                <!-- Detail Hero -->
                <section class="grid gap-8 lg:grid-cols-2">
                    <div class="space-y-5">
                        <div class="overflow-hidden rounded-3xl border border-gray-100 bg-gray-50">
                            <ImageWithLoader :src="getImg(props.item.image)" :alt="props.item.name"
                                class="h-full w-full" img-class="h-full w-full object-cover" loading="lazy" />
                        </div>
                    </div>

                    <aside class="flex h-max flex-col gap-6 rounded-3xl bg-white md:px-6 md:py-6 px-0 py-0">
                        <div class="space-y-2">
                            <span v-if="props.category"
                                class="inline-flex w-max items-center rounded-full bg-primary-50 px-3 py-1 text-xs font-semibold text-primary-700">
                                {{ props.category.name }}
                            </span>
                            <h1 class="text-2xl font-semibold text-gray-900">
                                {{ props.item.name }}
                            </h1>
                            <dl class="mt-2 flex flex-wrap gap-x-6 gap-y-2 text-sm text-gray-500">
                                <div>
                                    <dt class="font-medium text-gray-700">Cập nhật</dt>
                                    <dd>{{ props.item.updated_human || 'Đang cập nhật' }}</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="rounded-2xl bg-primary-50/60 p-5 ring-1 ring-primary-100">
                            <p class="text-sm font-medium text-primary-800">Giá tham khảo</p>
                            <p class="mt-1 text-3xl font-semibold text-primary-900">{{ priceText }}</p>
                            <p class="mt-1 text-xs text-primary-700/70">
                                Liên hệ để nhận báo giá chi tiết và ưu đãi tốt nhất.
                            </p>
                        </div>

                        <div class="flex flex-col gap-3">
                            <Link v-if="props.item.slug && props.category?.slug"
                                :href="route('quick-booking.fill-info', { partner_category_slug: props.category?.slug, partner_child_category_slug: props.item.slug })"
                                class="inline-flex w-full items-center justify-center rounded-lg bg-primary-600 px-4 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-primary-700 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                                Thuê ngay
                            </Link>
                            <Link :href="route('contact.index')"
                                class="inline-flex w-full items-center justify-center rounded-lg border border-primary-100 bg-white px-4 py-3 text-primary-600 font-semibold text-base shadow-sm transition hover:bg-primary-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500 focus-visible:ring-offset-2">
                                Hỗ trợ tư vấn
                            </Link>
                        </div>

                        <ul class="space-y-2 rounded-2xl border border-gray-100 bg-gray-50 p-5 text-sm text-gray-600">
                            <li class="flex items-center gap-3">
                                <span
                                    class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                                Đối tác uy tín, đã được xác minh.
                            </li>
                            <li class="flex items-center gap-3">
                                <span
                                    class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                                Phục vụ chuyên nghiệp, tận tâm.
                            </li>
                            <li class="flex items-center gap-3">
                                <span
                                    class="inline-flex size-8 items-center justify-center rounded-full bg-white text-primary-600 shadow-sm">✓</span>
                                Giá cả cạnh tranh, minh bạch.
                            </li>
                        </ul>
                    </aside>
                </section>

                <!-- Product Overview -->
                <section class="space-y-8 rounded-3xl bg-white md:px-6 md:py-6 px-0 py-0 lg:p-10">
                    <div class="grid gap-8 md:grid-cols-2 items-start">
                        <header class="space-y-2">
                            <h2 class="text-xl font-semibold text-gray-900">Mô tả chi tiết</h2>
                            <p class="text-sm text-gray-500">
                                Thông tin chi tiết về dịch vụ và đối tác.
                            </p>
                        </header>
                        <div class="space-y-3">
                            <h2 class="text-base font-semibold text-gray-900">Video</h2>
                            <div v-if="videoEmbed"
                                class="video-embed aspect-video w-full overflow-hidden rounded-2xl border border-gray-100 bg-gray-50">
                                <div class="h-full w-full" v-html="videoEmbed"></div>
                            </div>
                            <div v-else class="text-gray-500 italic">
                                Chưa có video.
                            </div>
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div v-if="hasDescription" class="prose prose-sm max-w-none text-gray-700"
                            v-html="descriptionToShow"></div>
                        <div v-else class="text-gray-500 italic">
                            Chưa có mô tả chi tiết.
                        </div>
                    </div>

                    <section class="space-y-3">
                        <h2 class="text-base font-semibold text-gray-900">Thông tin chính</h2>
                        <dl class="grid gap-4 sm:grid-cols-2 md:grid-cols-3">
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                                <dt class="font-semibold text-gray-700">Loại</dt>
                                <dd class="mt-1 text-gray-600">Sự kiện</dd>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                                <dt class="font-semibold text-gray-700">Lĩnh vực</dt>
                                <dd class="mt-1 text-gray-600">{{ props.category?.name || '—' }}</dd>
                            </div>
                            <div class="rounded-2xl border border-gray-100 bg-gray-50 px-4 py-3 text-sm">
                                <dt class="font-semibold text-gray-700">Kiểu đối tác</dt>
                                <dd class="mt-1 text-gray-600">{{ props.item.name }}</dd>
                            </div>
                        </dl>
                    </section>
                </section>

                <!-- Related Products -->
                <section v-if="relatedItems.length"
                    class="space-y-5 rounded-3xl bg-white p-6 lg:p-10 md:px-6 md:py-6 px-0 py-0">
                    <header class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h2 class="text-xl font-semibold text-gray-900">Có thể bạn quan tâm</h2>
                            <p class="text-sm text-gray-500">Các đối tác khác trong cùng danh mục.</p>
                        </div>
                    </header>

                    <Swiper :modules="[Navigation, Autoplay]" :slides-per-view="1.2" :space-between="12"
                        :navigation="true" :autoplay="autoplayOptions" :loop="relatedItems.length > 4"
                        :breakpoints="swiperBreakpoints" class="!pb-2 home-swiper-nav">
                        <SwiperSlide v-for="item in relatedItems" :key="item.id" class="!h-auto">
                            <article
                                class="group flex h-full flex-col overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-xs transition hover:-translate-y-1 hover:shadow-md">
                                <CardItem :card-item="item.card" :route-href="item.href" />
                                <div class="flex flex-1 flex-col gap-2 px-4 py-4">
                                    <h4 class="line-clamp-2 text-base font-semibold text-gray-900">{{ item.name }}</h4>
                                    <p class="text-sm text-gray-500">{{ props.category?.name }}</p>
                                    <div class="mt-auto flex items-center justify-between pt-2">
                                        <span class="text-sm font-semibold text-primary-700">{{ item.priceText }}</span>
                                        <Link :href="item.href"
                                            class="text-xs font-semibold text-primary-600 hover:text-primary-700">
                                            Chi tiết
                                        </Link>
                                    </div>
                                </div>
                            </article>
                        </SwiperSlide>
                    </Swiper>
                </section>
            </div>
        </section>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
// Note: The previous import structure was a bit loose, cleaning it up while adding new import
import { Swiper, SwiperSlide } from 'swiper/vue';
import { Navigation, Autoplay } from 'swiper/modules';
import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import CardItem from '@/pages/home/components/CardItem/index.vue';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { getImg } from '../booking/helper';
import 'swiper/css';
import 'swiper/css/navigation';
import '@/pages/home/styles/swiper-nav.css';
import { tutorialQuickLinks } from '@/lib/tutorial-links';
import { useTutorialHelper } from '@/lib/tutorial-helper';
import { inject } from "vue";

const route = inject('route') as any;

interface Item {
    id: number;
    name: string;
    slug: string;
    min_price: number | null;
    max_price: number | null;
    description: string | null;
    video_url?: string | null;
    updated_human?: string | null;
    image: string | null;
}
interface SimpleCat {
    id: number;
    name: string;
    slug: string;
}
interface RelatedItem {
    id: number;
    name: string;
    slug: string;
    min_price: number | null;
    max_price: number | null;
    image: string | null;
}

interface Props {
    item: Item;
    category: SimpleCat | null;
    related: RelatedItem[];
}
const props = defineProps<Props>();
const { addTutorialRoutes } = useTutorialHelper();

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

const pageTitle = computed(() => `${props.item.name} - ${props.category?.name ?? 'Sự kiện'}`);

const descriptionToShow = computed(() => props.item.description || '');
const hasDescription = computed(() => {
    if (!descriptionToShow.value) return false;
    const textOnly = descriptionToShow.value
        .replace(/<[^>]*>/g, ' ')
        .replace(/&nbsp;/gi, ' ')
        .trim();
    return textOnly.length > 0;
});
const videoEmbed = computed(() => (props.item.video_url ?? '').trim());

const swiperBreakpoints = {
    640: { slidesPerView: 2, spaceBetween: 12 },
    768: { slidesPerView: 3, spaceBetween: 16 },
    1024: { slidesPerView: 3.2, spaceBetween: 20 },
};

const autoplayOptions = {
    delay: 5000,
    disableOnInteraction: false,
    pauseOnMouseEnter: true,
};

const relatedItems = computed(() =>
    props.related.map((r: RelatedItem) => {
        let pText = 'Liên hệ';
        if (r.min_price && r.max_price) {
            if (r.min_price === r.max_price) pText = money(r.min_price);
            else pText = `${money(r.min_price)} - ${money(r.max_price)}`;
        } else if (r.min_price) {
            pText = money(r.min_price);
        } else if (r.max_price) {
            pText = money(r.max_price);
        }

        return {
            id: r.id,
            name: r.name,
            priceText: pText,
            href: route('partner-categories.show', r.slug),
            card: {
                id: r.id,
                name: r.name,
                slug: r.slug,
                image: r.image,
                description: null, // Related items from controller don't have description
            },
        };
    })
);

const page = usePage();
const user = computed(() => page.props.auth?.user ?? null);

// Set tutorial routes based on user authentication status
watch(
    user,
    (value) => {
        setTutorialRoutesBasedOnAuth(value);
    },
    { immediate: true }
);

function setTutorialRoutesBasedOnAuth(value: any) {
    if (!value) {
        addTutorialRoutes([tutorialQuickLinks.clientRegister, tutorialQuickLinks.partnerRegister, tutorialQuickLinks.clientRegisterAndFastBooking]);
    }
    else {
        addTutorialRoutes([tutorialQuickLinks.clientQuickOrder]);
    }
}
</script>

<style scoped>
.video-embed :deep(iframe) {
    width: 100%;
    height: 100%;
    border: 0;
    display: block;
}
</style>
