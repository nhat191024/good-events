<template>

    <Head class="font-lexend" title="Trang chủ" />

    <ClientAppHeaderLayout :background-class-names="'bg-primary-100'">

        <HeroBanner :banner-images="isMobile ? heroBannerMobileImages : heroBannerImages">
            <HeroContentBlock tag-label="Sự kiện" title="Tổ chức sự kiện thật dễ dàng!"
                :description="settings.hero_title ?? 'Bạn đã đến đúng nơi rồi đấy. Kết nối với hàng trăm đối tác dịch vụ sự kiện uy tín, chuyên nghiệp cho mọi nhu cầu của bạn.'"
                :primary-cta="{ label: 'Khám phá', href: '#search' }"
                :secondary-cta="{ label: 'Chi tiết', href: route('about.index') }" :stats="[
                    { value: '450+', label: 'Đối tác uy tín' },
                    { value: '98%', label: 'Khách hàng hài lòng' },
                    { value: '24/7', label: 'Hỗ trợ trực tuyến' }
                ]" />
        </HeroBanner>

        <PartnerCategoryIcons :categories="categories" />

        <HomeCtaBanner />

        <div id="search"
            class="container-fluid p-2 sm:p-4 md:p-6 space-y-12 w-full max-w-7xl bg-white/10 backdrop-blur-lg border border-white/20 rounded-xl scroll-mt-24">
            <div class="max-w-5xl mx-auto">
                <SearchBar :show-search-btn="false" v-model="search" />
                <div v-if="keywordSuggestions.length" class="mt-3 flex flex-wrap gap-2">
                    <button v-for="suggestion in keywordSuggestions" :key="suggestion" type="button"
                        class="rounded-full border border-primary-200 bg-white px-3 py-1 text-xs font-medium text-primary-700 hover:bg-primary-50 transition-colors"
                        @click="search = suggestion">
                        {{ suggestion }}
                    </button>
                </div>
            </div>
            <div v-if="isSearchMode && isSearching" class="text-center text-sm text-gray-500">
                Đang tìm kiếm kết quả...
            </div>
            <div v-else-if="isSearchMode && activeEventCategories.length === 0"
                class="text-center text-sm text-gray-500">
                Không tìm thấy danh mục phù hợp.
            </div>
            <div v-for="eventCategory in activeEventCategories" :key="eventCategory.id">
                <CategorySection :category-name="eventCategory.name" :category-slug="eventCategory.slug"
                    :has-more-children="eventCategory.total_children_count > pagination.childLimit"
                    :partner-categories="activePartnerCategories[eventCategory.id] || []" />
            </div>
            <div v-if="hasMoreCategories" ref="loadMoreTrigger"
                class="flex w-full items-center justify-center py-8 text-sm text-gray-500">
                <template v-if="isLoadingMore">
                    <svg class="mr-2 h-5 w-5 animate-spin text-primary-500" xmlns="http://www.w3.org/2000/svg"
                        fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    Đang tải thêm danh mục...
                </template>
                <span v-else>Cuộn xuống để xem thêm danh mục</span>
            </div>
            <p v-else-if="!isSearchMode" class="text-center text-sm text-gray-500">Đã hiển thị tất cả danh mục.</p>
        </div>
    </ClientAppHeaderLayout>
</template>

<script setup lang="ts">
import { ref, computed, onMounted, onBeforeUnmount, watch } from 'vue';
import axios from 'axios';
import { Head, Link } from '@inertiajs/vue3';
import ClientAppHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue'
import HeroBanner from './partials/HeroBanner.vue';
import HeroContentBlock from './components/HeroContentBlock.vue';
import PartnerCategoryIcons from './partials/PartnerCategoryIcons/index.vue';
import CategorySection from './partials/CategorySection.vue';
import HomeCtaBanner from './components/HomeCtaBanner.vue';
import { PartnerCategory } from '@/types/database';
import { normText } from '@/lib/search-filter';
import { useSearchSuggestion } from '@/lib/useSearchSuggestion';
import SearchBar from '../categories/partials/SearchBar.vue';

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

interface HomeSearchResponse {
    eventCategories: ParentCategory[];
    partnerCategories: Record<number, PartnerCategory[]>;
}

type PartnerCategoryMap = Record<number, PartnerCategory[]>;
type PartnerCategoryInputMap = Record<number, PartnerCategory[] | Record<string, PartnerCategory>>;

const normalizePartnerCategories = (input?: PartnerCategoryInputMap | null): PartnerCategoryMap => {
    if (!input) return {};
    const normalized: PartnerCategoryMap = {};
    Object.entries(input).forEach(([key, value]) => {
        if (Array.isArray(value)) {
            normalized[Number(key)] = value;
            return;
        }
        if (value && typeof value === 'object') {
            normalized[Number(key)] = Object.values(value);
            return;
        }
        normalized[Number(key)] = [];
    });
    return normalized;
};

const props = defineProps<Props>();
const pagination = computed(() => props.pagination);

const categories = [
    {
        id: 1,
        name: 'Nhân sự',
        slug: 'su-kien',
        icon: 'mdi:flower',
        image: '/images/home/logo-su-kien.webp',
        href: route('home')
    },
    {
        id: 2,
        name: 'Thiết kế',
        slug: 'tai-lieu',
        icon: 'mdi:book-open',
        image: '/images/home/logo-tai-lieu.webp',
        href: route('asset.home')
    },
    {
        id: 3,
        name: 'Thiết bị SK',
        slug: 'tim-khach-san',
        icon: 'mdi:bed',
        image: '/images/home/logo-loa-dai.webp',
        href: route('rent.home')
    },
    {
        id: 4,
        name: 'Địa điểm',
        slug: 'khach-san',
        icon: 'mdi:bag-personal',
        image: '/images/home/logo-dia-diem.webp',
        href: route('blog.discover')
    },
    {
        id: 5,
        name: 'Hướng dẫn',
        slug: 'huong-dan',
        icon: 'mdi:bag-personal',
        image: '/images/home/logo-huong-dan.webp',
        href: route('blog.guides.discover')
    },
    {
        id: 6,
        name: 'Kiến thức',
        slug: 'kien-thuc',
        icon: 'mdi:bag-personal',
        image: '/images/home/logo-kien-thuc.webp',
        href: route('blog.knowledge.discover')
    },
];

const fetchHomeSearch = async (q: string): Promise<HomeSearchResponse> => {
    const { data } = await axios.get(route('home.search'), {
        params: { q },
    });
    const response = data as HomeSearchResponse & { partnerCategories?: PartnerCategoryInputMap };
    return {
        ...response,
        partnerCategories: normalizePartnerCategories(response.partnerCategories),
    };
};

const {
    query: search,
    result: searchResult,
    loading: isSearching,
    hasQuery: hasSearchQuery,
} = useSearchSuggestion<PartnerCategory, HomeSearchResponse>({
    fetcher: fetchHomeSearch,
    minLength: 2,
    debounceMs: 300,
});

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
const partnerCategoriesStore = ref<PartnerCategoryMap>(
    normalizePartnerCategories(props.partnerCategories)
);

const isSearchMode = computed(() => hasSearchQuery.value);

const activeEventCategories = computed(() => {
    if (!isSearchMode.value) return eventCategoryList.value;
    return searchResult.value?.eventCategories ?? [];
});

const activePartnerCategories = computed(() => {
    if (!isSearchMode.value) return partnerCategoriesStore.value;
    return searchResult.value?.partnerCategories ?? {};
});

const categoryTotals = computed<Record<number, number>>(() => {
    const totals: Record<number, number> = {};
    eventCategoryList.value.forEach((cat) => {
        totals[cat.id] = cat.total_children_count ?? 0;
    });
    return totals;
});

const loadingChildrenIds = ref<Set<number>>(new Set());

const keywordSuggestions = computed(() => {
    const term = search.value.trim();
    if (term.length < 2) return [];

    const names = new Set<string>();
    const addNames = (
        items?:
            | { name?: string | null; parent_id?: number | null }[]
            | Record<string, { name?: string | null; parent_id?: number | null }>
            | null
    ) => {
        if (!items) return;
        const list = Array.isArray(items) ? items : Object.values(items);
        list.forEach((item) => {
            if (item?.parent_id === null) return;
            if (item?.name) names.add(item.name);
        });
    };

    const source = searchResult.value;
    if (source) {
        addNames(source.eventCategories);
        Object.values(source.partnerCategories ?? {}).forEach((arr) => addNames(arr));
    } else {
        addNames(eventCategoryList.value);
        Object.values(partnerCategoriesStore.value).forEach((arr) => addNames(arr));
    }

    const normalizedTerm = normText(term);
    return Array.from(names)
        .filter((name) => normText(name).includes(normalizedTerm))
        .slice(0, 8);
});

const isLoadingMore = ref(false);
const loadMoreTrigger = ref<HTMLElement | null>(null);
let observer: IntersectionObserver | null = null;

const hasMoreCategories = computed(() =>
    !isSearchMode.value && eventCategoryList.value.length < props.pagination.total
);

const hasMoreChildrenFor = (categoryId: number) => {
    const total = categoryTotals.value[categoryId] ?? 0;
    const loaded = partnerCategoriesStore.value[categoryId]?.length ?? 0;
    return loaded < total;
};

const loadMoreChildrenForCategory = async (categoryId: number, categorySlug: string) => {
    if (loadingChildrenIds.value.has(categoryId)) return;
    if (!hasMoreChildrenFor(categoryId)) return;

    const current = partnerCategoriesStore.value[categoryId] ?? [];
    loadingChildrenIds.value.add(categoryId);
    try {
        const { data } = await axios.get(route('home.category-children'), {
            params: {
                category_slug: categorySlug,
                offset: current.length,
                limit: pagination.value.childLimit,
            },
        });

        const merged = [...current, ...(data.children ?? [])];
        partnerCategoriesStore.value = {
            ...partnerCategoriesStore.value,
            [categoryId]: merged,
        };
    } catch (error) {
        console.error('Failed to load more partner categories', error);
    } finally {
        loadingChildrenIds.value.delete(categoryId);
    }
};

const loadMoreCategories = async () => {
    if (isLoadingMore.value || !hasMoreCategories.value || isSearchMode.value) return;
    isLoadingMore.value = true;
    try {
        const { data } = await axios.get(route('home.event-categories'), {
            params: {
                offset: eventCategoryList.value.length,
                limit: props.pagination.batchSize,
            },
        });
        const response = data as {
            eventCategories: ParentCategory[];
            partnerCategories?: PartnerCategoryInputMap;
        };
        eventCategoryList.value = [...eventCategoryList.value, ...response.eventCategories];
        partnerCategoriesStore.value = {
            ...partnerCategoriesStore.value,
            ...normalizePartnerCategories(response.partnerCategories),
        };
    } catch (error) {
        console.error('Failed to load more categories', error);
    } finally {
        isLoadingMore.value = false;
    }
};

const handlePartnerReachEnd = (categorySlug: string) => {
    if (isSearchMode.value) return;
    const category = eventCategoryList.value.find((cat) => cat.slug === categorySlug);
    if (!category) return;
    loadMoreChildrenForCategory(category.id, category.slug);
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
        partnerCategoriesStore.value = normalizePartnerCategories(newPartnerCategories);
    }
);

onBeforeUnmount(() => {
    cleanupObserver();
    unregisterResizeListener();
});
</script>
