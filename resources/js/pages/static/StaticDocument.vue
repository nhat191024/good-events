<template>
    <Head :title="page.title" />

    <ClientHeaderLayout>
        <div class="relative w-full overflow-hidden bg-gradient-to-r from-blue-800 via-blue-700 to-indigo-700 text-white">
            <div class="absolute -left-24 -top-20 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>
            <div class="absolute -bottom-16 right-12 h-72 w-72 rounded-full bg-white/10 blur-3xl"></div>

            <div class="relative mx-auto max-w-6xl px-6 py-16 md:px-10 lg:py-20">
                <p v-if="page.hero?.kicker" class="text-sm font-semibold uppercase tracking-[0.18em] text-blue-100">
                    {{ page.hero.kicker }}
                </p>
                <h1 class="mt-3 text-3xl font-bold leading-tight md:text-4xl lg:text-5xl">
                    {{ page.title }}
                </h1>
                <p v-if="page.intro" class="mt-4 max-w-7xl text-lg text-blue-50 md:text-xl">
                    {{ page.intro }}
                </p>
                <p v-else-if="page.hero?.note" class="mt-4 max-w-7xl text-lg text-blue-50 md:text-xl">
                    {{ page.hero.note }}
                </p>
            </div>
        </div>

        <div class="w-full bg-slate-50 pb-16 pt-10 md:pb-20 md:pt-14">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 md:grid-cols-[260px_1fr] md:px-8 lg:gap-12">
                <StaticSidebar :sections="page.sections" :active-id="activeSection" @select="scrollToSection" />

                <div class="space-y-6 md:space-y-8">
                    <StaticSection
                        v-for="(section, index) in page.sections"
                        :key="section.id"
                        :ref="registerSectionRef(section.id)"
                        :section="section"
                        :order="index + 1"
                    />
                </div>
            </div>
        </div>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { onBeforeUnmount, onMounted, ref } from 'vue';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

import StaticSection from './components/StaticSection.vue';
import StaticSidebar from './components/StaticSidebar.vue';
import type { StaticPagePayload } from './types';

const props = defineProps<{
    page: StaticPagePayload;
}>();

const activeSection = ref<string>(props.page.sections[0]?.id ?? '');
const sectionRefs = new Map<string, HTMLElement>();
const refHandlers = new Map<string, (el: HTMLElement | null) => void>();
let observer: IntersectionObserver | null = null;

const createObserver = () =>
    new IntersectionObserver(
        (entries) => {
            const visible = entries
                .filter((entry) => entry.isIntersecting)
                .sort(
                    (a, b) => (a.target as HTMLElement).getBoundingClientRect().top - (b.target as HTMLElement).getBoundingClientRect().top
                );

            if (visible.length === 0) {
                return;
            }

            const nextActive = (visible[0].target as HTMLElement).dataset.sectionId;
            if (nextActive) {
                activeSection.value = nextActive;
            }
        },
        {
            rootMargin: '-25% 0px -55% 0px',
            threshold: 0.2,
        }
    );

const registerSectionRef = (id: string) => {
    if (!refHandlers.has(id)) {
        refHandlers.set(id, (el: HTMLElement | { $el?: HTMLElement } | null) => {
            const element = el && '$el' in el ? el.$el : el;

            if (element instanceof HTMLElement) {
                sectionRefs.set(id, element);
                observer?.observe(element);
                return;
            }

            if (sectionRefs.has(id)) {
                const existing = sectionRefs.get(id);
                if (existing) {
                    observer?.unobserve(existing);
                }
                sectionRefs.delete(id);
            }
        });
    }

    return refHandlers.get(id);
};

const scrollToSection = (id: string) => {
    const target = sectionRefs.get(id);
    if (!target) {
        return;
    }

    const offset = 80;
    const top = window.scrollY + target.getBoundingClientRect().top - offset;

    window.scrollTo({ top, behavior: 'smooth' });
    activeSection.value = id;
};

onMounted(() => {
    observer = createObserver();
    sectionRefs.forEach((el) => observer?.observe(el));
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
    sectionRefs.clear();
});
</script>
