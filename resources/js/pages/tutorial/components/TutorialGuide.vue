<template>
    <Head :title="title" />

    <ClientHeaderLayout>
        <TutorialHero :title="title" :description="description" />

        <div v-if="$slots['after-hero']" class="bg-white">
            <div class="mx-auto max-w-7xl px-4 py-4 sm:px-6 lg:px-8">
                <slot name="after-hero" />
            </div>
        </div>

        <div class="bg-slate-50 pb-16 pt-10 md:pb-20 md:pt-14">
             <div class="mx-auto grid max-w-7xl gap-6 px-4 lg:grid-cols-[260px_1fr] md:px-8 lg:gap-12">
                <aside class="md:sticky md:top-28 md:h-max">
                    <div class="hidden lg:block rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
                        <div class="border-b border-slate-100 px-5 py-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.12em] text-slate-500">Mục lục</p>
                            <p class="mt-1 text-sm text-slate-600">Chọn mục để chuyển nhanh đến nội dung tương ứng.</p>
                        </div>
                        <nav class="flex flex-col divide-y divide-slate-100">
                            <a
                                v-for="section in sections"
                                :key="section.id"
                                :href="`#${section.id}`"
                                class="flex items-start gap-3 px-5 py-4 text-left transition hover:bg-slate-50 focus:outline-none"
                                :class="activeSection === section.id ? 'bg-blue-50 text-blue-700' : 'text-slate-700'"
                                :aria-current="activeSection === section.id ? 'true' : 'false'"
                                @click.prevent="scrollToSection(section.id)"
                            >
                                <span
                                    class="flex h-7 w-7 items-center justify-center rounded-full text-xs font-semibold"
                                    :class="activeSection === section.id ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-500'"
                                >
                                    {{ section.code }}
                                </span>
                                <span class="text-sm leading-6">{{ section.title }}</span>
                            </a>
                        </nav>
                    </div>
                </aside>

                <div class="space-y-6 md:space-y-8">
                    <motion.section
                        v-for="section in sections"
                        :key="section.id"
                        :id="section.id"
                        :data-section-id="section.id"
                        :ref="registerSectionRef(section.id)"
                        :class="[
                            'rounded-2xl p-6 shadow-sm ring-1 ring-slate-100 md:p-8 transition',
                            highlightedSection === section.id
                                ? 'bg-primary-100 ring-2 ring-blue-300 shadow-lg shadow-blue-200/60'
                                : 'bg-white',
                        ]"
                        :style="{ scrollMarginTop: `${SCROLL_OFFSET + 20}px` }"
                        :initial="'rest'"
                        :animate="highlightedSection === section.id ? 'highlight' : 'rest'"
                        :variants="spotlightVariants"
                    >
                        <div class="flex items-start gap-4">
                            <span class="mt-1 flex h-9 w-9 flex-none items-center justify-center rounded-full bg-blue-50 text-sm font-semibold text-blue-700">
                                {{ section.code }}
                            </span>
                            <div class="space-y-4">
                                <div>
                                    <h2 class="text-xl font-semibold text-slate-900">{{ section.title }}</h2>
                                </div>
                                <div class="relative w-full overflow-hidden rounded-xl bg-slate-100 shadow-inner">
                                    <div class="aspect-video w-full">
                                        <iframe
                                            class="h-full w-full"
                                            :src="`https://www.youtube.com/embed/${section.youtubeId}?rel=0`"
                                            :title="section.title"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen
                                            loading="lazy"
                                        ></iframe>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </motion.section>
                </div>
            </div>
        </div>

        <div v-if="$slots['after-content']" class="bg-white pb-10">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <slot name="after-content" />
            </div>
        </div>

        <!-- Mobile quick TOC launcher -->
        <div class="lg:hidden">
            <motion.button
                type="button"
                class="fixed bottom-5 left-4 z-[90] inline-flex items-center gap-2 rounded-full bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/30 ring-1 ring-blue-500/60 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300"
                :while-hover="{ scale: 1.04, y: -2 }"
                :while-tap="{ scale: 0.98 }"
                :transition="{ type: 'spring', stiffness: 300, damping: 18 }"
                @click="openMobileToc"
            >
                Mục lục
            </motion.button>
        </div>
    </ClientHeaderLayout>

    <!-- Mobile TOC overlay -->
    <Teleport to="body">
        <div v-if="isMobileTocOpen" class="fixed inset-0 z-[95]">
            <motion.div
                class="absolute inset-0 bg-slate-900/40 backdrop-blur-[2px]"
                :initial="{ opacity: 0 }"
                :animate="{ opacity: 1 }"
                :exit="{ opacity: 0 }"
                :transition="{ duration: 0.2 }"
                @click="closeMobileToc"
            />
            <motion.div
                class="absolute inset-x-0 bottom-0 max-h-[75vh] rounded-t-2xl bg-white shadow-2xl shadow-slate-900/20 ring-1 ring-slate-200"
                :initial="{ y: '100%' }"
                :animate="{ y: 0 }"
                :exit="{ y: '100%' }"
                :transition="{ type: 'spring', stiffness: 260, damping: 26 }"
            >
                <div class="flex items-center justify-between border-b border-slate-100 px-4 py-3">
                    <p class="text-sm font-semibold text-slate-800">Mục lục</p>
                    <button
                        type="button"
                        class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600 shadow-inner ring-1 ring-slate-200"
                        @click="closeMobileToc"
                    >
                        Đóng
                    </button>
                </div>
                <div class="max-h-[65vh] overflow-y-auto p-4 space-y-2">
                    <button
                        v-for="section in sections"
                        :key="section.id"
                        type="button"
                        class="w-full rounded-xl border border-slate-200 px-4 py-3 text-left transition hover:border-blue-200 hover:bg-blue-50/60 active:scale-[0.99] focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-300"
                        @click="handleMobileSelect(section.id)"
                    >
                        <div class="flex items-center gap-3">
                            <span class="flex h-7 w-7 flex-none items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                                {{ section.code }}
                            </span>
                            <span class="text-sm leading-6 text-slate-800">{{ section.title }}</span>
                        </div>
                    </button>
                </div>
            </motion.div>
        </div>
    </Teleport>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { motion } from 'motion-v';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';
import { slugifyTutorial } from '@/lib/tutorial-helper';

import TutorialHero from './TutorialHero.vue';

interface TutorialSection {
    id?: string;
    code: string;
    title: string;
    youtubeId: string;
}

const props = defineProps<{
    title: string;
    description: string;
    sections: TutorialSection[];
}>();

const sections = props.sections.map((section) => ({
    ...section,
    id: section.id ?? slugifyTutorial(section.title),
}));

const activeSection = ref<string>(sections[0]?.id ?? '');
const highlightedSection = ref<string | null>(null);
const isMobileTocOpen = ref(false);
const sectionRefs = new Map<string, HTMLElement>();
const refHandlers = new Map<string, (el: HTMLElement | null) => void>();
let observer: IntersectionObserver | null = null;
let highlightTimeout: number | null = null;

const SCROLL_OFFSET = 100;
const HIGHLIGHT_DURATION = 5000;
const spotlightVariants = {
    rest: { scale: 1, transition: { duration: 0.2 } },
    highlight: {
        scale: 1.02,
        transition: {
            duration: 0.6,
            repeat: 7,
            repeatType: 'mirror',
            ease: 'easeInOut',
        },
    },
};

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

const scrollToSection = (id: string, updateHash = true, behavior: ScrollBehavior = 'smooth') => {
    const target = sectionRefs.get(id);
    if (!target) {
        return;
    }

    const top = window.scrollY + target.getBoundingClientRect().top - SCROLL_OFFSET;

    window.scrollTo({ top, behavior });
    activeSection.value = id;
    triggerHighlight(id);

    if (updateHash) {
        history.replaceState(null, '', `#${id}`);
    }
};

const triggerHighlight = (id: string) => {
    highlightedSection.value = id;
    if (highlightTimeout) {
        window.clearTimeout(highlightTimeout);
    }
    highlightTimeout = window.setTimeout(() => {
        highlightedSection.value = null;
    }, HIGHLIGHT_DURATION);
};

const handleHashChange = () => {
    const hash = decodeURIComponent(window.location.hash.replace('#', ''));
    if (hash && sectionRefs.has(hash)) {
        // Use a small delay so the browser finishes any default jump first.
        window.requestAnimationFrame(() => scrollToSection(hash, false, 'auto'));
    }
};

const openMobileToc = () => {
    isMobileTocOpen.value = true;
};

const closeMobileToc = () => {
    isMobileTocOpen.value = false;
};

const handleMobileSelect = (id: string) => {
    closeMobileToc();
    // Delay slightly so the panel has time to dismiss before scrolling.
    window.requestAnimationFrame(() => scrollToSection(id));
};

onMounted(async () => {
    observer = createObserver();
    sectionRefs.forEach((el) => observer?.observe(el));

    await nextTick();
    handleHashChange();
    window.addEventListener('hashchange', handleHashChange);
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
    sectionRefs.clear();
    if (highlightTimeout) {
        window.clearTimeout(highlightTimeout);
    }
    window.removeEventListener('hashchange', handleHashChange);
});
</script>
