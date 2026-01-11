<template>
    <Head :title="title" />

    <ClientHeaderLayout>
        <TutorialHero :title="title" :description="description" />

        <div class="bg-slate-50 pb-16 pt-10 md:pb-20 md:pt-14">
            <div class="mx-auto grid max-w-7xl gap-6 px-4 md:grid-cols-[260px_1fr] md:px-8 lg:gap-12">
                <aside class="md:sticky md:top-28 md:h-max">
                    <div class="rounded-2xl bg-white shadow-sm ring-1 ring-slate-100">
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
                    <section
                        v-for="section in sections"
                        :key="section.id"
                        :id="section.id"
                        :data-section-id="section.id"
                        :ref="registerSectionRef(section.id)"
                        class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-100 md:p-8"
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
                    </section>
                </div>
            </div>
        </div>
    </ClientHeaderLayout>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { nextTick, onBeforeUnmount, onMounted, ref } from 'vue';

import ClientHeaderLayout from '@/layouts/app/ClientHeaderLayout.vue';

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

const slugify = (value: string) =>
    value
        .toLowerCase()
        .replace(/đ/g, 'd')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');

const sections = props.sections.map((section) => ({
    ...section,
    id: section.id ?? slugify(section.title),
}));

const activeSection = ref<string>(sections[0]?.id ?? '');
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

const scrollToSection = (id: string, updateHash = true) => {
    const target = sectionRefs.get(id);
    if (!target) {
        return;
    }

    const offset = 96;
    const top = window.scrollY + target.getBoundingClientRect().top - offset;

    window.scrollTo({ top, behavior: 'smooth' });
    activeSection.value = id;

    if (updateHash) {
        history.replaceState(null, '', `#${id}`);
    }
};

onMounted(async () => {
    observer = createObserver();
    sectionRefs.forEach((el) => observer?.observe(el));

    await nextTick();
    const hash = window.location.hash.replace('#', '');
    if (hash && sectionRefs.has(hash)) {
        scrollToSection(hash, false);
    }
});

onBeforeUnmount(() => {
    observer?.disconnect();
    observer = null;
    sectionRefs.clear();
});
</script>
