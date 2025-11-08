<template>
    <section class="bg-gradient-to-b from-sky-50 via-white to-white">
        <div
            class="mx-auto flex w-full max-w-6xl flex-col gap-12 px-4 py-16 sm:px-6 lg:flex-row lg:items-center lg:gap-16 lg:px-8">
            <div class="flex-1 space-y-6">
                <p class="text-sm font-semibold uppercase tracking-widest text-sky-600">{{ content.eyebrow }}</p>
                <h1 class="text-4xl font-bold leading-tight text-slate-900 sm:text-5xl">
                    {{ content.title }}
                </h1>
                <p class="text-lg text-slate-600">
                    {{ content.description }}
                </p>
                <div class="rounded-3xl border border-slate-100 bg-white/80 p-4 shadow-sm shadow-black/[0.03]">
                    <p class="text-sm font-semibold uppercase tracking-wide text-slate-500">{{ content.subheading }}</p>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <span
                            v-for="service in servicePills"
                            :key="service.label"
                            class="inline-flex items-center rounded-full border border-slate-200 bg-white px-4 py-1 text-sm font-medium text-slate-700">
                            {{ service.label }}
                        </span>
                    </div>
                </div>
                <div class="flex flex-wrap gap-3">
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center justify-center rounded-2xl bg-sky-600 px-6 py-3 text-base font-semibold text-white shadow-sm shadow-sky-600/30 transition hover:bg-sky-500">
                        Đăng ký ngay
                    </Link>
                    <Link
                        :href="route('register')"
                        class="inline-flex items-center justify-center rounded-2xl border border-slate-200 px-6 py-3 text-base font-semibold text-slate-700 transition hover:border-slate-300">
                        Tặng Voucher bạn mới
                    </Link>
                </div>
            </div>

            <div class="flex-1 space-y-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <article
                        v-for="highlight in highlights"
                        :key="highlight.title"
                        class="rounded-2xl border border-slate-100 bg-white/90 p-5 shadow-sm shadow-black/[0.03]">
                        <p class="text-sm font-semibold uppercase tracking-wide text-sky-600">
                            {{ highlight.kicker }}
                        </p>
                        <h3 class="mt-2 text-lg font-semibold text-slate-900">{{ highlight.title }}</h3>
                        <p class="mt-2 text-sm text-slate-600">
                            {{ highlight.description }}
                        </p>
                    </article>
                </div>
                <div class="rounded-3xl border border-dashed border-sky-200 bg-white/80 p-6 shadow-lg shadow-sky-100/60">
                    <p class="text-base font-semibold uppercase tracking-widest text-slate-500">Cam kết</p>
                    <p class="mt-2 text-xl font-semibold text-slate-900">
                        Chọn dịch vụ biểu diễn phù hợp chỉ trong 30 giây với quy trình minh bạch và bảo chứng chất lượng.
                    </p>
                    <p class="mt-3 text-sm text-slate-600">
                        Từ đặt lịch, kết nối, vận hành đến đánh giá sau sự kiện đều có đội ngũ Sukientot theo sát 24/7.
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

interface Highlight {
    kicker: string;
    title: string;
    description: string;
}

interface HeroContent {
    eyebrow: string;
    title: string;
    description: string;
    subheading: string;
}

interface ServicePill {
    label: string;
}

defineProps<{
    content: HeroContent;
    highlights: Highlight[];
    servicePills: ServicePill[];
}>();
</script>
