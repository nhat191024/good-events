<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';
import { Head } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import HeroBanner from './partials/HeroBanner.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';
import CategorySection from './partials/CategorySection.vue';
import HomeCtaBanner from './components/HomeCtaBanner.vue';
import { PartnerCategory } from '@/types/database';
import { createSearchFilter } from '@/lib/search-filter';

type ParentCategory = PartnerCategory & { total_children_count?: number };

interface BannerImage {
    responsive_image?: string | null;
}

interface BannerImageWrapper {
  data: BannerImage[];
}

interface PaginationMeta {
    total: number;
    initialLimit: number;
    batchSize: number;
    childLimit: number;
}

interface Props {
    eventCategories: ParentCategory[];
    partnerCategories: { [key: number]: PartnerCategory[] };
    pagination: PaginationMeta;
    settings: {
        app_name: string;
        hero_title?: string | null;
        banner_images: BannerImageWrapper;
        mobile_banner_images: BannerImageWrapper;
    };
}

const props = defineProps<Props>();
const pagination = computed(() => props.pagination);

const categories = [
    {
        id: 1,
        name: 'Nhân sự',
        slug: 'su-kien',
        icon: 'mdi:flower',
        image: '/images/logo-su-kien.webp',
        href: route('home')
    },
    {
        id: 2,
        name: 'Thiết kế',
        slug: 'tai-lieu',
        icon: 'mdi:book-open',
        image: '/images/logo-tai-lieu.webp',
        href: route('asset.home')
    },
    {
        id: 3,
        name: 'Thiết bị SK',
        slug: 'tim-khach-san',
        icon: 'mdi:bed',
        image: '/images/logo-loa-dai.webp',
        href: route('rent.home')
    },
    {
        id: 4,
        name: 'Địa điểm',
        slug: 'khach-san',
        icon: 'mdi:bag-personal',
        href: route('blog.discover')
    },
    {
        id: 5,
        name: 'Hướng dẫn',
        slug: 'huong-dan',
        icon: 'mdi:bag-personal',
        href: route('blog.guides.discover')
    },
    {
        id: 6,
        name: 'Kiến thức',
        slug: 'kien-thuc',
        icon: 'mdi:bag-personal',
        href: route('blog.knowledge.discover')
    },
];

const search = ref('');

const heroBannerImages = computed(() => {
    const images = props.settings.banner_images.data ?? [];
    return images;
});

const heroBannerMobileImages = computed(() => {
    const images = props.settings.mobile_banner_images.data ?? [];
    return images;
});

const MOBILE_BREAKPOINT_PX = 768;
const isMobile = ref(false);
const updateIsMobile = () => {
    if (typeof window === 'undefined') {
        isMobile.value = false;
        return;
    }
    isMobile.value = window.innerWidth <= MOBILE_BREAKPOINT_PX;
};

const eventCategoryList = ref<ParentCategory[]>([...props.eventCategories]);
const partnerCategoriesStore = ref<Record<number, PartnerCategory[]>>({ ...props.partnerCategories });

const filteredPartnerCategories = computed(() => {
    const source = partnerCategoriesStore.value;
    if (!search.value.trim()) return source;
    const filter = createSearchFilter(['name', 'slug'], search.value.trim());
    const result: Record<number, PartnerCategory[]> = {};
    for (const catId in source) {
        const arr = source[catId].filter(filter);
        if (arr.length) result[Number(catId)] = arr;
    }
    return result;
});

const isLoadingMore = ref(false);
const loadMoreTrigger = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const hasMoreCategories = computed(() => eventCategoryList.value.length < props.pagination.total);

const loadMoreCategories = async () => {
    if (isLoadingMore.value || !hasMoreCategories.value) return;
    isLoadingMore.value = true;
    try {
        const { data } = await axios.get(route('home.event-categories'), {
            params: {
                offset: eventCategoryList.value.length,
                limit: props.pagination.batchSize,
            },
        });
        eventCategoryList.value = [...eventCategoryList.value, ...data.eventCategories];
        partnerCategoriesStore.value = {
            ...partnerCategoriesStore.value,
            ...data.partnerCategories,
        };
    } catch (error) {
        console.error('Failed to load more categories', error);
    } finally {
        isLoadingMore.value = false;
    }
};

const cleanupObserver = () => {
    if (observer) {
        observer.disconnect();
        observer = null;
    }
};

const initObserver = () => {
    if (typeof window === 'undefined' || !('IntersectionObserver' in window)) return;
    if (!loadMoreTrigger.value) return;
    cleanupObserver();
    observer = new IntersectionObserver(
        (entries) => {
            if (entries.some((entry) => entry.isIntersecting)) {
                loadMoreCategories();
            }
        },
        { rootMargin: '200px' }
    );
    observer.observe(loadMoreTrigger.value);
};

const registerResizeListener = () => {
    if (typeof window === 'undefined') return;
    updateIsMobile();
    window.addEventListener('resize', updateIsMobile);
};

const unregisterResizeListener = () => {
    if (typeof window === 'undefined') return;
    window.removeEventListener('resize', updateIsMobile);
};

onMounted(() => {
    initObserver();
    registerResizeListener();
});

watch(() => loadMoreTrigger.value, () => {
    initObserver();
});

watch(hasMoreCategories, (value) => {
    if (value) {
        initObserver();
    } else {
        cleanupObserver();
    }
});

watch(
    () => props.eventCategories,
    (newEventCategories) => {
        eventCategoryList.value = [...newEventCategories];
    }
);

watch(
    () => props.partnerCategories,
    (newPartnerCategories) => {
        partnerCategoriesStore.value = { ...newPartnerCategories };
    }
);

onBeforeUnmount(() => {
    cleanupObserver();
    unregisterResizeListener();
});
</script>

<template>

    <Head class="font-lexend" title="Trang chủ" />

    <ClientAppHeaderLayout :background-class-names="'bg-primary-100'">

        <HeroBanner
            v-model="search"
            :header-text="settings.hero_title ?? 'Thuê đối tác xịn. Sự kiện thêm vui'"
            :banner-images="isMobile ? heroBannerMobileImages : heroBannerImages"
        />

        <PartnerCategoryIcons :categories="categories" />

        <HomeCtaBanner />

        <div class="container mx-auto px-4 py-8 space-y-12">
            <div v-for="eventCategory in eventCategoryList" :key="eventCategory.id">
                <CategorySection :category-name="eventCategory.name" :category-slug="eventCategory.slug"
                    :has-more-children="eventCategory.total_children_count > pagination.childLimit"
                    :partner-categories="filteredPartnerCategories[eventCategory.id] || []" />
            </div>
            <div v-if="hasMoreCategories" ref="loadMoreTrigger"
                class="flex w-full items-center justify-center py-8 text-sm text-gray-500">
                <template v-if="isLoadingMore">
                    <svg class="mr-2 h-5 w-5 animate-spin text-primary-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Đang tải thêm danh mục...
                </template>
                <span v-else>Cuộn xuống để xem thêm danh mục</span>
            </div>
            <p v-else class="text-center text-sm text-gray-500">Đã hiển thị tất cả danh mục.</p>
        </div>
    </ClientAppHeaderLayout>
</template>
