<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/vue3';
import { inject, onMounted, ref } from 'vue';

interface Props {
    showNav?: boolean;
}

withDefaults(defineProps<Props>(), {
    showNav: () => true,
});

const route = inject('route') as any;
const BANNER_HIDE_KEY = 'skt_hide_install_banner_until';
const BANNER_HIDE_DURATION_MS = 2 * 60 * 60 * 1000;
const showInstallBanner = ref(true);

const hideInstallBanner = () => {
    showInstallBanner.value = false;
    if (typeof window === 'undefined') return;
    const until = Date.now() + BANNER_HIDE_DURATION_MS;
    window.localStorage.setItem(BANNER_HIDE_KEY, String(until));
};

const shouldShowInstallBanner = () => {
    if (typeof window === 'undefined') return true;
    const raw = window.localStorage.getItem(BANNER_HIDE_KEY);
    if (!raw) return true;
    const until = Number(raw);
    if (Number.isNaN(until)) {
        window.localStorage.removeItem(BANNER_HIDE_KEY);
        return true;
    }
    if (Date.now() >= until) {
        window.localStorage.removeItem(BANNER_HIDE_KEY);
        return true;
    }
    return false;
};

onMounted(() => {
    showInstallBanner.value = shouldShowInstallBanner();
});
</script>

<template>
    <div v-if="showInstallBanner"
        :class="['sticky z-40 w-full border-b border-primary-500/20 bg-primary-700 text-white', showNav ? 'top-16' : 'top-0']">
        <div class="mx-auto flex max-w-7xl flex-col gap-3 px-4 py-2.5 sm:flex-row sm:items-center">
            <div class="flex items-start gap-2 text-sm font-medium">
                <span class="mt-1 inline-flex h-2 w-2 flex-shrink-0 rounded-full bg-white/80"></span>
                <p class="leading-relaxed">
                    Cài đặt APP Sự kiện tốt để nhận ưu đãi cho đơn đặt đầu tiên
                </p>
            </div>
            <div class="flex items-center gap-2 sm:ml-auto">
                <Button as-child variant="outlineWhite" size="sm" class="h-8 px-4 text-primary-700">
                    <Link :href="route('static.download-app')">Cài đặt</Link>
                </Button>
                <button type="button" aria-label="Đóng banner"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-full text-white/80 transition hover:bg-white/15 hover:text-white"
                    @click="hideInstallBanner">
                    ×
                </button>
            </div>
        </div>
    </div>
</template>
