<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import PartnerContactCard from '@/pages/profile/partner/components/PartnerContactCard.vue'
import PartnerQuickStatsCard from '@/pages/profile/partner/components/PartnerQuickStatsCard.vue'
import PartnerServiceCard from '@/pages/profile/partner/components/PartnerServiceCard.vue'
import PartnerIntroCard from '@/pages/profile/partner/components/PartnerIntroCard.vue'
import PartnerReviewsCard from '@/pages/profile/partner/components/PartnerReviewsCard.vue'
import PartnerImagesCard from '@/pages/profile/partner/components/PartnerImagesCard.vue'
import axios from 'axios'
import { getImg } from '@/pages/booking/helper';
import ImageWithLoader from '@/components/ImageWithLoader.vue';
import { X, Flag, ExternalLink } from 'lucide-vue-next'
import ReportModal from '@/components/ReportModal.vue'
import { inject } from "vue";
import PartnerVideoCard from '@/pages/profile/partner/components/PartnerVideoCard.vue'

const route = inject('route') as any;

type UserInfo = {
    id: number; name: string; avatar_url: string; location: string | null; avatar_img_tag?: string;
    joined_year: string | null; is_pro: boolean; rating: number; total_reviews: number; total_customers: number | null;
    is_verified?: boolean; is_legit?: boolean;
}
type Media = { id: number; url: string }
type Service = { id: number; name: string | null; field: string | null; price: number | null; media: Media[] }
type Review = { id: number; author: string; rating: number; review: string | null; created_human: string | null }
type Payload = {
    user: UserInfo;
    stats: { customers: number; years: number; satisfaction_pct: string; avg_response: string };
    quick: { orders_placed: number; completed_orders: number; cancelled_orders_pct: string; last_active_human: string | null };
    contact: { phone: string | null; email: string | null; response_time: string; languages: string[]; timezone: string };
    services: Service[];
    reviews: Review[];
    intro: string | null;
    video_url: string | null;
}

const props = defineProps<{
    userId: number | null
}>()

const open = defineModel<boolean>('open', { default: false })
const isReportModalOpen = ref(false)

const data = ref<Payload | null>(null)
const status = ref<'idle' | 'loading' | 'success' | 'error'>('idle')
const cache = new Map<number, Payload>()

watch([open, () => props.userId], async ([isOpen, id]) => {
    if (!isOpen || !id) return

    if (cache.has(id)) {
        data.value = cache.get(id) as Payload
        status.value = 'success'
        return
    }

    status.value = 'loading'
    try {
        const url = route('client-orders.partner-profile', { user: id })
        const response = await axios.get<Payload>(url, { headers: { 'Accept': 'application/json' } })
        const payload = response.data
        cache.set(id, payload)
        data.value = payload
        status.value = 'success'
    } catch (err) {
        console.error(err)
        status.value = 'error'
    }
}, { immediate: false })

const user = computed(() => data.value?.user)
</script>

<template>
    <!-- root overlay -->
    <div v-if="open" class="fixed inset-0 z-[100]">
        <!-- backdrop -->
        <div class="absolute inset-0 bg-black/40" @click="open = false"></div>

        <!-- scroll container: cho phép overlay tự cuộn nếu panel quá cao -->
        <div class="relative z-10 h-full w-full overflow-y-auto">
            <!-- center the panel, tạo khoảng cách top/bottom -->
            <div class="flex min-h-full items-start justify-center p-4">
                <!-- modal panel: dùng flex-col + max-h và overflow-hidden để body cuộn -->
                <div
                    class="w-full max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden flex flex-col max-h-[90vh] max-h-[90svh] md:max-h-[85vh] md:max-h-[85svh]">
                    <!-- header: sticky để luôn hiện khi cuộn -->
                    <div class="flex items-center justify-between px-4 py-3 border-b sticky top-0 bg-white z-10">
                        <div class="flex items-center gap-3">
                            <ImageWithLoader v-if="user?.avatar_url" :src="getImg(user!.avatar_url)" :alt="user!.name" :img-tag="user!.avatar_img_tag"
                                class="w-10 h-10 rounded-full" img-class="w-10 h-10 rounded-full object-cover"
                                loading="lazy" />
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <div class="font-semibold">{{ user?.name ?? '—' }}</div>
                                    <span v-if="user?.is_verified"
                                        class="px-2 py-0.5 bg-slate-100 text-slate-700 text-xs rounded border border-slate-200"
                                        title="Người dùng này đã xác minh email"
                                        aria-label="Người dùng này đã xác minh email">
                                        Đã xác minh
                                    </span>
                                    <span v-if="user?.is_legit"
                                        class="px-2 py-0.5 bg-amber-100 text-amber-800 text-xs rounded border border-amber-200"
                                        title="Người dùng đã xác minh thông tin cá nhân chuẩn thông qua KYC"
                                        aria-label="Người dùng đã xác minh thông tin cá nhân chuẩn thông qua KYC">
                                        Đáng tin cậy
                                    </span>
                                </div>
                                <div class="text-xs text-muted-foreground">
                                    Thành viên • {{ user?.joined_year ? `từ ${user?.joined_year}` : '—'
                                    }}
                                </div>
                            </div>
                        </div>
                        <button class="p-2 rounded hover:bg-gray-100" @click="open = false" aria-label="Đóng hồ sơ">
                            <X class="w-4 h-4" />
                        </button>
                    </div>

                    <!-- body: phần này mới là phần cuộn, giữ header đứng yên -->
                    <div class="flex-1 min-h-0 overflow-y-auto overscroll-contain p-4">
                        <div v-if="status === 'loading'" class="py-10 text-center text-sm text-muted-foreground">
                            đang tải hồ sơ...
                        </div>

                        <div v-else-if="status === 'error'" class="py-10 text-center text-sm text-red-500">
                            không tải được hồ sơ. thử lại sau nhé.
                        </div>

                        <template v-else-if="status === 'success' && data">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-4">
                                <div class="md:col-span-12 space-y-4">
                                    <a
                                        v-if="user?.id"
                                        target="_blank"
                                        :href="route('profile.client.show', { user: user.id })"
                                        class="w-full cursor-pointer flex items-center justify-center gap-2 rounded-xl bg-slate-50 p-3 text-sm font-medium text-slate-700 transition-colors hover:bg-slate-100 ring ring-gray-200"
                                    >
                                        <ExternalLink class="h-4 w-4" />
                                        Mở trong trang  mới
                                    </a>
                                    <button @click="isReportModalOpen = true"
                                        class="w-full cursor-pointer flex items-center justify-center gap-2 rounded-xl bg-red-50 p-3 text-sm font-medium text-red-600 transition-colors hover:bg-red-100">
                                        <Flag class="h-4 w-4" />
                                        Báo cáo người dùng này
                                    </button>
                                </div>
                                <!-- sidebar -->
                                <div class="md:col-span-4 space-y-4">
                                    <PartnerContactCard :contact="data.contact" />
                                    <PartnerQuickStatsCard :quick="data.quick" />
                                </div>
                                <!-- main -->
                                <div class="md:col-span-8 space-y-4">
                                    <PartnerIntroCard :intro="data.intro" :stats="data.stats" />
                                    <PartnerVideoCard :iframe="data.video_url" />
                                    <PartnerServiceCard :services="data.services" />
                                    <PartnerReviewsCard :items="data.reviews" />
                                </div>

                                <div class="md:col-span-12 space-y-4">
                                    <PartnerImagesCard :services="data.services" />
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <ReportModal v-model:open="isReportModalOpen" :user-id="user?.id" />
</template>


